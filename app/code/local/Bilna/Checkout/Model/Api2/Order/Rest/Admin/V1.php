<?php
/**
 * API2 class for coupon (admin)
 *
 * @category   Bilna
 * @package    Bilna_Checkout
 * @author     Development Team <development@bilna.com>
 */

use Pheanstalk\Pheanstalk;

class Bilna_Checkout_Model_Api2_Order_Rest_Admin_V1 extends Bilna_Checkout_Model_Api2_Order_Rest {
    protected $_paymentMethodHelper;
    protected $_paymentMethodCc = [];
    protected $_paymentMethodVtdirect = [];
    protected $_paymentMethodVa = [];

    /**
     * Covert Quote to Order
     *
     * @param  $quoteId
     * @param  $store
     * @return bool
     */
    protected function _create(array $data) {
        $quoteId = $data['entity_id'];
        $storeId = isset($data['store_id']) ? $data['store_id'] : self::DEFAULT_STORE_ID;
        $tokenId = isset($data['token_id']) ? $data['token_id'] : '';
        $payment = isset($data['payment']) ? $data['payment'] : '';
        $trxFrom = isset($data['trx_from']) ? $data['trx_from'] : self::DEFAULT_TRX_FROM;
        
        $allowInstallment = isset($data['allow_installment']) ? $data['allow_installment'] : '';
        $installmentMethod = isset($data['installment_method']) ? $data['installment_method'] : '';
        $installmentTenor = isset($data['installment']) ? $data['installment'] : '';

        try {
            $store = $this->_getStore();
            $quote = $this->_getQuote($quoteId, $store);
            
            if ($quote->getIsMultiShipping()) {
                throw Mage::throwException('Invalid Checkout Type');
            }
            
            if ($quote->getCheckoutMethod() == Mage_Checkout_Model_Api_Resource_Customer::MODE_GUEST && !Mage::helper('checkout')->isAllowedGuestCheckout($quote, $quote->getStoreId())) {
                throw Mage::throwException('Guest Checkout is not Enable');
            }

             /** @var $customerResource Mage_Checkout_Model_Api_Resource_Customer */
            $customerResource = Mage::getModel("checkout/api_resource_customer");
            $isNewCustomer = $customerResource->prepareCustomerForQuote($quote);

            if (!empty ($payment)) {
                $quote->getPayment()->importData($payment);
            }

            $paymentCode = $quote->getPayment()->getMethodInstance()->getCode();
            
            if ($installmentTenor) {
                $quoteItems = $quote->getAllItems();
                $item_ids = array ();

                foreach ($quoteItems as $item) {
                    $item_ids[] = $item->getProductId();
                }

                $installmentOptionType = Mage::getStoreConfig('payment/' . $paymentCode . '/installment_option');

                /**
                 * installment type is per order
                 */
                if ($installmentOptionType == 2) {
                    if ($installmentTenor == '') {
                        throw Mage::throwException('Please select an installment type before placing the order.');
                    }

                    if ($installmentTenor > 1) {
                        foreach ($quoteItems as $item) {
                            $item->setInstallment($allowInstallment);
                            $item->setInstallmentMethod($installmentMethod);
                            $item->setInstallmentType($installmentTenor);
                            $item->save();
                        }
                    }

                    if ($installmentTenor == $this->getPaymentTypeTransaction($paymentCode, 'full')) {
                        $payType = $this->getPaymentTypeTransaction($paymentCode, 'full');
                    }
                    else {
                        $payType = $this->getPaymentTypeTransaction($paymentCode, 'installment');
                    }
                }
                else {
                    //if installment type is per item
                    /*foreach ($quoteItems as $item) 
                    {
                        $item->setInstallment($allowInstallment);
                        $item->setInstallmentMethod($installmentMethod);
                        $item->setInstallmentType($installmentTenor);
                        $item->save();
                    }*/
                    //API doesn't support installment per item, coz we need to evaluate existing code module @bilna_paymenthod
                }
                
                $quote->setPayType($payType)->save();
                //$quote->setCcBins($this->getRequest()->getPost('cc_bins', false))->save();
            }/*else{
                throw Mage::throwException('Please select an installment type before placing the order.');
            }*/

            $payType = $this->getPaymentTypeTransaction($paymentCode, 'full');
            $quote->setPayType($payType);
            $quote->collectTotals();
            
            /* checking customer using their poinst or not*/
            if (isset ($payment['use_points']) && $payment['use_points'] > 0) {
                $this->pointsCheck($quote, $payment);
            }
            
            /** @var $service Mage_Sales_Model_Service_Quote */
            $service = Mage::getModel('sales/service_quote', $quote);
            $service->submitAll();

            if ($isNewCustomer) {
                try {
                    $customerResource->involveNewCustomer($quote);
                } catch (Exception $e) {
                    Mage::logException($e);
                }
            }

            $order = $service->getOrder();            
            
            /**
             *  
             * setTrxFrom: to determine where is transaction came from.
             * 
             * Option: 
             * - 1 is from logan apps
             * - 2 is from mobile apps
             * - 3 is from magento apps
             * 
             */
            $saveOrder = false;
            if(!empty($trxFrom)) {
                $order->setTrxFrom($trxFrom);
                $saveOrder = true;
            }
            
            if (isset ($payment['use_points']) && $payment['use_points'] > 0) {
                $order = $this->submitPoints($order, $payment);
                $saveOrder = true;
            }
            
            if($saveOrder) {
                $order->save();
            }

            if ($order) {
                //- for FDS
                Mage::dispatchEvent('checkout_type_onepage_save_order_after', array ('order' => $order, 'quote' => $quote));

                try {
                    $order->sendNewOrderEmail();
                } catch (Exception $e) {
                    Mage::logException($e);
                }
            }

            Mage::dispatchEvent('checkout_submit_all_after', array ('order' => $order, 'quote' => $quote));

            $orderId = $order->getId();
            $paymentCode = $order->getPayment()->getMethodInstance()->getCode();
            $orderIncrementId = $order->getIncrementId();
            $orderGrandTotal = $order->getGrandTotal();
            $orderCanceled = $this->_getOrderCanceled($order);

            if (in_array($paymentCode, $this->getPaymentMethodCc()) && ($orderCanceled === false)) {
                $charge = Mage::getModel('paymethod/api')->creditcardCharge($order, $tokenId);
                $order->setState(Mage_Sales_Model_Order::STATE_NEW, self::ORDER_STATUS_PENDING_INVOICE);
                $order->save();
                $this->_storeChargeDataToQueue($charge);
            }
            elseif (in_array($paymentCode, $this->getPaymentMethodVtdirect()) && ($orderCanceled === false)) {
                $charge = Mage::getModel('paymethod/api')->vtdirectRedirectCharge($order);
                $this->_addHistoryOrder($order, $charge['response']->status_message);
                $this->_storeChargeDataToQueue($charge, false);
            }
            
            if (in_array($paymentCode, $this->getPaymentMethodVA()) && ($orderCanceled === false)) {
                $charge = Mage::getModel('paymethod/api')->vtdirectVaCharge($order);
                $this->_addHistoryOrder($order, $charge['response']->status_message);
                $this->_storeChargeDataToQueue($charge, false);
            }
            
            Mage::dispatchEvent('checkout_onepage_controller_success_action', array ('order_ids' => array ($orderId), 'order' => $order));
        }
        catch (Mage_Core_Exception $e) {
            $this->_critical($e->getMessage());
        }

        return $this->_getLocation($order);

    }

    protected function submitPoints($order, $payment) {
        if ($order->getCustomerIsGuest())  {
            return $order;
        }

        if ($order->getCustomerId()) {
            $quote = $order->getQuote();
            
            if (!$quote instanceof Mage_Sales_Model_Quote) {
                $quote = Mage::getModel('sales/quote')
                    ->setSharedStoreIds(array ($order->getStoreId()))
                    ->load($order->getQuoteId());
            }
            
            $sum = floatval($quote->getData('base_subtotal_with_discount'));
            $limitedPoints = Mage::helper('points')->getLimitedPoints($sum, $order->getCustomer(), $order->getStoreId());

            $pointsAmount = (int) $payment['points_amount'];
            $customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
            $customerPoints = Mage::getModel('points/summary')->loadByCustomer($customer)->getPoints();

            if ($customerPoints < $pointsAmount || $limitedPoints < $pointsAmount || !Mage::helper('points')->isAvailableToRedeem($pointsAmount)) {
                if($limitedPoints < $pointsAmount && !$quote->getCouponCode()) {
                    Mage::throwException('Incorrect points amount');
                }
            }

            $amountToSubtract = -$pointsAmount;
            $moneyForPointsBase = Mage::getModel('points/api')->changePointsToMoney($amountToSubtract, $customer, $order->getStore()->getWebsite());
            $moneyForPoints = $order->getBaseCurrency()->convert($moneyForPointsBase, $order->getOrderCurrencyCode());

            $order->setGrandTotal($order->getGrandTotal() + $moneyForPoints);
            $order->setBaseGrandTotal($order->getBaseGrandTotal() + $moneyForPointsBase );            
            $order->setAmountToSubtract($amountToSubtract);
            $order->setBaseMoneyForPoints($moneyForPointsBase);
            $order->setMoneyForPoints($moneyForPoints);
        }

        return $order;
    }

    protected function pointsCheck($quote, $payment) {
        if ($quote->getCustomerIsGuest()) 
        {
            return $quote;
        }

        if ($quote->getCustomerId())
        {
            if (!$quote instanceof Mage_Sales_Model_Quote) {
                $quote = Mage::getModel('sales/quote')
                        ->setSharedStoreIds(array($quote->getStoreId()))
                        ->load($quote->getId());
            }
            $sum = floatval($quote->getData('base_subtotal_with_discount'));
            $limitedPoints = Mage::helper('points')->getLimitedPoints($sum, $quote->getCustomer(), $quote->getStoreId());

            $pointsAmount = (int) $payment['points_amount'];
            $customer = Mage::getModel('customer/customer')->load($quote->getCustomerId());
            $customerPoints = Mage::getModel('points/summary')->loadByCustomer($customer)->getPoints();

            if (
                    $customerPoints < $pointsAmount ||
                    $limitedPoints < $pointsAmount ||
                    !Mage::helper('points')->isAvailableToRedeem($pointsAmount)
            ) {
                if($limitedPoints < $pointsAmount && !$quote->getCouponCode()) {
                    Mage::throwException('Incorrect points amount');
                }
            }

        }

        return $quote;
    }
    
    protected function getPaymentMethodHelper() {
        if (!$this->_paymentMethodHelper) {
            $this->_paymentMethodHelper = Mage::helper('paymethod');
        }
        
        return $this->_paymentMethodHelper;
    }

    protected function getPaymentMethodCc() {
        if (!$this->_paymentMethodCc) {
            $this->_paymentMethodCc = $this->getPaymentMethodHelper()->getPaymentMethodCc();
        }
        
        return $this->_paymentMethodCc;
    }

    protected function getPaymentMethodVtdirect() {
        if (!$this->_paymentMethodVtdirect) {
            $this->_paymentMethodVtdirect = $this->getPaymentMethodHelper()->getPaymentMethodVtdirect();
        }
        
        return $this->_paymentMethodVtdirect;
    }
    
    protected function getPaymentMethodVa() {
        if (!$this->_paymentMethodVa) {
            $this->_paymentMethodVa = $this->getPaymentMethodHelper()->getPaymentMethodVA();
        }
        
        return $this->_paymentMethodVa;
    }

    protected function getPaymentTypeTransaction($paymentCode, $type) {
        if ($paymentCode == 'klikpay') {
            if ($type == 'full') {
                return Bilna_Paymethod_Model_Method_Klikpay::PAYMENT_TYPE_FULL_TRANSACTION;
            }
            else if ($type == 'installment') {
                return Bilna_Paymethod_Model_Method_Klikpay::PAYMENT_TYPE_INSTALLMENT_TRANSACTION;
            }
            else {
                return Bilna_Paymethod_Model_Method_Klikpay::PAYMENT_TYPE_COMBINE_TRANSACTION;
            }
        }
        else if ($paymentCode == 'anzcc') {
            if ($type == 'full') {
                return Bilna_Anzcc_Model_Anzcc::PAYMENT_TYPE_FULL_TRANSACTION;
            }
            else if ($type == 'installment') {
                return Bilna_Anzcc_Model_Anzcc::PAYMENT_TYPE_INSTALLMENT_TRANSACTION;
            }
            else {
                return Bilna_Anzcc_Model_Anzcc::PAYMENT_TYPE_COMBINE_TRANSACTION;
            }
        }
        else {
            return '';
        }
    }
    
    protected function _getOrderCanceled($order) {
        return (strtolower($order->getData('status')) == 'canceled');
    }
    
    protected function _addHistoryOrder($order, $message) {
        $order->addStatusHistoryComment($message);
        $order->save();
    }
    
    protected function _storeChargeDataToQueue($charge, $invoice = true) {
        try {
            $hostname = Mage::getStoreConfig('bilna_queue/beanstalkd_settings/hostname');
            $pheanstalk = new Pheanstalk($hostname);
            $pheanstalk->useTube('vt_charge')->put(json_encode($charge)); //- store charge request-response for API Charging Info

            if ($invoice) {
                $pheanstalk->useTube('invoice')->put(json_encode($charge['response']), '', 60); //- store charge reseponse for create invoice or cancel order (delay 1 minute)
            }
        }
        catch (Exception $e) {
            Mage::logException($e);
        }
    }
}
