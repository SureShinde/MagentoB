<?php
/**
 * Description of Bilna_Paymethod_VtdirectController
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Paymethod_VtdirectController extends Mage_Core_Controller_Front_Action {
    protected $_code = 'vtdirect';
    protected $_typeTransaction = 'transaction';
    
    public function thankyouAction() {
        $session = Mage::helper('paymethod/vtdirect')->getCheckout();
        
        if (!$session->getLastSuccessQuoteId()) {
            $this->_redirect('checkout/cart');
            return;
        }

        $lastQuoteId = $session->getLastQuoteId();
        $lastOrderId = $session->getLastOrderId();
        $lastRecurringProfiles = $session->getLastRecurringProfileIds();
        
        if (!$lastQuoteId || (!$lastOrderId && empty ($lastRecurringProfiles))) {
            $this->_redirect('checkout/cart');
            return;
        }
        
        /**
         * charge credit card
         */
        $responseCharge = array ();
        
        if ($this->getOrderId()) {
            $order = $this->getOrder();
            $items = $order->getAllItems();
            
            $url = Mage::getStoreConfig('payment/vtdirect/charge_transaction_url');
            $data = array (
                'token_id' => $this->getTokenId(),
                'order_id' => $this->maxChar($order->getIncrementId(), 20),
                'order_items' => $this->getOrderItems($items),
                'gross_amount' => round($order->getGrandTotal()),
                'email' => $this->getCustomerEmail($order->getBillingAddress()->getEmail()),
                'shipping_address' => $this->parseShippingAddress($order->getShippingAddress()),
                'billing_address' => $this->parseBillingAddress($order->getBillingAddress())
            );
            $responseCharge = json_decode(Mage::helper('paymethod/vtdirect')->postRequest($url, $data));
            
            $contentRequest = sprintf("%s | request_vtdirect: %s", $order->getIncrementId(), json_encode($data));
            $contentResponse = sprintf("%s | response_vtdirect: %s", $order->getIncrementId(), json_encode($responseCharge));
            $this->writeLog($this->_typeTransaction, 'charge', $contentRequest);
            $this->writeLog($this->_typeTransaction, 'charge', $contentResponse);
            
            /**
             * processing order
             */
            $this->updateOrder($order, $responseCharge);
        }
        else {
            $this->_redirect('checkout/cart');
            return;
        }
        
        /**
         * assign data to View
         */
        Mage::register('response_charge', $responseCharge);
        
        $this->loadLayout();
        $this->getLayout()->getBlock('head')->setTitle($this->__('Thankyou Page'));
        $this->renderLayout();
    }
    
    private function getOrderId() {
        return Mage::helper('paymethod/vtdirect')->getOrderId();
    }
    
    private function getOrder() {
        return Mage::getModel('sales/order')->load($this->getOrderId());
    }
    
    private function getTokenId() {
        $tokenId = Mage::getSingleton('core/session')->getVtdirectTokenId();
        
        /**
         * remove token_id session
         */
        Mage::getSingleton("core/session")->unsVtdirectTokenIdCreate();
        Mage::getSingleton("core/session")->unsVtdirectTokenId();
        
        return $tokenId;
    }
    
    private function getOrderItems($items) {
        $result = array ();
        
        if (count($items) > 0) {
            foreach ($items as $itemId => $item) {
                $result[$itemId]['id'] = $this->maxChar($item->getProductId(), 20);
                $result[$itemId]['price'] = round($item->getPrice());
                $result[$itemId]['qty'] = $item->getQtyToInvoice();
                $result[$itemId]['name'] = $this->maxChar($this->removeSymbols($item->getName()), 20);
            }
        }
        
        return $result;
    }
    
    private function getCustomerEmail($email) {
        if (Mage::getStoreConfig('payment/vtdirect/development_testing')) {
            return 'vt-testing@veritrans.co.id';
        }
        
        return $email;
    }
    
    private function parseShippingAddress($shippingAddress) {
        $result = array (
            'first_name' => $this->maxChar($shippingAddress->getFirstname(), 20),
            'last_name' => $this->maxChar($shippingAddress->getLastname(), 20),
            'address1' => $this->maxChar($shippingAddress->getStreet(1), 100),
            'address2' => $this->maxChar($shippingAddress->getStreet(2), 100),
            'city' => $this->maxChar($shippingAddress->getCity(), 20),
            'postal_code' => $this->maxChar($shippingAddress->getPostcode(), 10),
            'phone' => $this->maxChar($shippingAddress->getTelephone(), 19)
        );
        
        return $result;
    }
    
    private function parseBillingAddress($billingAddress) {
        $result = array (
            'first_name' => $this->maxChar($billingAddress->getFirstname(), 20),
            'last_name' => $this->maxChar($billingAddress->getLastname(), 20),
            'address1' => $this->maxChar($billingAddress->getStreet(1), 100),
            'address2' => $this->maxChar($billingAddress->getStreet(2), 100),
            'city' => $this->maxChar($billingAddress->getCity(), 20),
            'postal_code' => $this->maxChar($billingAddress->getPostcode(), 10),
            'phone' => $this->maxChar($billingAddress->getTelephone(), 19)
        );
        
        return $result;
    }
  
    private function maxChar($text, $maxLength = 10) {
        if (empty ($text)) {
            return '';
        }
        
        return substr($text, 0, $maxLength);
    }
    
    private function removeSymbols($text) {
        return Mage::helper('paymethod/vtdirect')->removeSymbols($text);
    }
    
    private function updateOrder($order, $responseCharge) {
        if ($responseCharge->status == 'success') {
            if ($order->canInvoice()) {
                $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();

                if ($invoice->getTotalQty()) {
                    $invoice->register();
                    $transactionSave = Mage::getModel('core/resource_transaction')
                        ->addObject($invoice)
                        ->addObject($invoice->getOrder());
                    $transactionSave->save();                            
                    $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true, $responseCharge->message, true)->save();
                    $invoice->sendEmail(true, '');

                    return true;
                }
            }

            return false;
        }
        else if ($responseCharge->status == 'challenge') {
            $order->setState(Mage_Sales_Model_Order::STATE_NEW, 'cc_verification', $responseCharge->message, true);
            $order->save();
            
            return true;
        }
        else if ($responseCharge->status == 'failure') {
            $order->addStatusHistoryComment($responseCharge->message);
            $order->save();
            
            return true;
        }
        else {
            //do nothing
            return true;
        }
    }
    
    protected function writeLog($type, $logFile, $content) {
        $tdate = date('Ymd', Mage::getModel('core/date')->timestamp(time()));
        $filename = sprintf("%s_%s.%s", $this->_code, $logFile, $tdate);
        
        return Mage::helper('paymethod')->writeLogFile($this->_code, $type, $filename, $content);
    }
}
