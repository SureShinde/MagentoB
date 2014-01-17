<?php
/**
 * Description of Bilna_Paymethod_KlikpayController
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Paymethod_KlikpayController extends Mage_Core_Controller_Front_Action {
    protected $_code = 'klikpay';
    protected $_file = 'inquiry';
    protected $_klikpayUserId = '';
    protected $_additionalData = '';
    protected $_transactionNo = '';
    protected $_signature = '';
    
    protected $_typeTransaction = 'transaction';
    protected $_typeConfirmation = 'confirmation';
    
    public function redirectAction() {
        $this->loadLayout();
        $this->renderLayout();
    }
    
    public function inquiryAction() {
        if (isset ($_GET['klikPayCode'])) {
            $this->_klikpayUserId = $_GET['klikPayCode'];
        }
        
        if (isset ($_GET['additionalData'])) {
            $this->_additionalData = $_GET['additionalData'];
        }
        
        if (isset ($_GET['transactionNo'])) {
            $this->_transactionNo = $_GET['transactionNo'];
        }
        
        if (isset ($_GET['signature'])) {
            $this->_signature = $_GET['signature'];
        }
                
        $contentLog = sprintf("%s | request_klikpay: %s", $this->_transactionNo, json_encode($_GET));
        $this->writeLog($this->_typeTransaction, 'inquiry', $contentLog);
        
        $orderCollection = Mage::getModel('sales/order')->getCollection();
        $orderCollection->addFieldToFilter('status', 'pending');
        $orderCollection->addFieldToFilter('increment_id', array ('eq' => $this->_transactionNo));
        $orderCollection->getSelect()->join(array ('SFOP' => 'sales_flat_order_payment'), 'SFOP.method = "klikpay" AND main_table.entity_id = SFOP.parent_id', array ('method'), null, 'left');
        
        $order = $orderCollection->getFirstItem();
        $validationHours = Mage::getStoreConfig('payment/klikpay/order_validation');
        $validationOption = Mage::getStoreConfig('payment/klikpay/order_validation_option');

        if ($order->getId()) {
            //checking order validate order
            if ($validationHours) {
                $orderCreatedDate = $order->getCreatedAt();
                $orderCreatedDate = date('Y-m-d H:i:s', strtotime($orderCreatedDate . ' +' . $validationHours . ' ' . $validationOption));
                $now = now();
                
                if (strtotime($now) > strtotime($orderCreatedDate)) {
                    $contentLog = sprintf("%s | order is not valid.", $this->_transactionNo);
                    $this->writeLog($this->_typeTransaction, 'inquiry', $contentLog);
                    die ('order is not valid.');
                }
            }
            
            //checking signature
            if ($this->_signature != $order->getKlikpaySignature()) {
                $contentLog = sprintf("%s | signature is not valid.", $this->_transactionNo);
                $this->writeLog($this->_typeTransaction, 'inquiry', $contentLog);
                die ('signature is not valid.');
            }

            $items = $order->getAllItems();
            
            if (count($items) > 0) {
                header('Content-type: text/xml; charset=utf-8');    
                
                $xml  = '<?xml version="1.0" encoding="utf-8"?>';
                $xml .= '<OutputListTransactionIPAY>';
                $xml .= "<klikPayCode>" . $this->_klikpayUserId . "</klikPayCode>";
                $xml .= "<transactionNo>" . $this->_transactionNo . "</transactionNo>";
                $xml .= "<currency>IDR</currency>";
                
                $_miscFee = false;
                
                foreach ($items as $itemId => $item) {
                    $_installmentTypeValue = Mage::helper('paymethod/klikpay')->getInstallmentOption($item->getInstallmentType(), 'value');
                    
                    if ($_installmentTypeValue == Bilna_Paymethod_Model_Method_Klikpay::PAYMENT_TYPE_FULL_TRANSACTION) {
                        $xml .= "<fullTransaction>";
                        $xml .= "<amount>" . number_format((int) $item->getRowTotal(), 2, null, '') . "</amount>";
                        $xml .= "<description>" . Mage::helper('paymethod/klikpay')->_removeSymbols($item->getName()) . "</description>";
                        $xml .= "</fullTransaction>";
                    }
                    else {
                        $_tenor = Mage::helper('paymethod/klikpay')->getInstallmentOption($item->getInstallmentType(), 'tenor');
                        $_merchantId = Mage::helper('paymethod/klikpay')->getInstallmentOption($item->getInstallmentType(), 'merchantid');
                        
                        $xml .= "<installmentTransaction>";
                        $xml .= "<itemName>" . Mage::helper('paymethod/klikpay')->_removeSymbols($item->getName()) . "</itemName>";
                        $xml .= "<quantity>" . number_format($item->getQtyOrdered()) . "</quantity>";
                        $xml .= "<amount>" . number_format((int) $item->getRowTotal(), 2, null, '') . "</amount>";
                        $xml .= "<tenor>" . $_tenor . "</tenor>";
                        $xml .= "<codePlan>000</codePlan>";
                        $xml .= "<merchantId>" . $_merchantId . "</merchantId>";
                        $xml .= "</installmentTransaction>";
                        $_miscFee = true;
                    }
                }
                
                $xml .= $_miscFee === false ? "<miscFee></miscFee>" : "<miscFee>" . number_format((int) $order->getShippingAmount(), 2, null, '') . "</miscFee>";
                $xml .= "<additionalData>" . $this->_additionalData . "</additionalData>";
                $xml .= "</OutputListTransactionIPAY>";
                
                $contentLog = sprintf("%s | response_klikpay: %s", $this->_transactionNo, $xml);
                $this->writeLog($this->_typeTransaction, 'inquiry', $contentLog);
                die ($xml);
            }
        }
        
        die();
    }
    
    protected function writeLog($type, $logFile, $content) {
        $tdate = date('Ymd', Mage::getModel('core/date')->timestamp(time()));
        $filename = sprintf("%s_%s.%s", $this->_code, $logFile, $tdate);
        
        return Mage::helper('paymethod')->writeLogFile($this->_code, $type, $filename, $content);
    }
}
