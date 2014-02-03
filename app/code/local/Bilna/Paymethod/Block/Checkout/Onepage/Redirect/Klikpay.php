<?php
/**
 * Description of Bilna_Paymethod_Block_Checkout_Onepage_Redirect_Klikpay
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Paymethod_Block_Checkout_Onepage_Redirect_Klikpay extends Mage_Core_Block_Template {
    protected $_klikpayUserId = '';
    protected $_transactionNo = '';
    protected $_transactionDate = '';
    protected $_transactionAmount = '';
    protected $_currency = 'IDR';
    protected $_payType = '';
    protected $_callBackUrl = '';
    protected $_description = '';
    protected $_clearKey = '';
    protected $_miscFee = '';
    protected $_signature = '';
    
    protected function _getCheckout() {
        return Mage::getSingleton('checkout/session');
    }

    protected function _getOrder() {
        if ($this->getOrder()) {
            return $this->getOrder();
        }
        else if ($this->getRequest()->getParam('id')) {
            return Mage::getModel('sales/order')->loadByIncrementId($this->getRequest()->getParam('id'));
        }
        else if ($orderIncrementId = $this->_getCheckout()->getLastRealOrderId()) {
            return Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);
        }
        else {
            return null;
        }
    }
    
    public function getFormData() {
        $order = $this->_getOrder();
        $payment = $order->getPayment();
        $this->_klikpayUserId = Mage::getStoreConfig('payment/klikpay/klikpay_user_id');//$payment->getKlikpayUserId();
        $this->_transactionNo = $order->getIncrementId();
        $this->_transactionAmount = number_format((int) $order->getGrandTotal(), 2, null, '');
        $this->_payType = $order->getPayType();
        $this->_callBackUrl = Mage::getStoreConfig('payment/klikpay/call_back_url');
        $this->_transactionDate = date('d/m/Y H:i:s', strtotime($order->getCreatedAt()));
        $this->_clearKey = Mage::getStoreConfig('payment/klikpay/klikpay_clearkey'); //Mage::helper('core')->uniqHash();
        $this->_signature = Mage::helper('paymethod/klikpay')->signature($this->_klikpayUserId,$this->_transactionNo, $this->_currency, $this->_clearKey, $this->_transactionDate, $this->_transactionAmount);
        
        $data = array (
            'klikPayCode' => $this->_klikpayUserId,
            'transactionNo' => $this->_transactionNo,
            'totalAmount' => $this->_transactionAmount,
            'currency' => $this->_currency,
            'payType' => $this->_payType,
            'callback' => $this->_callBackUrl . $this->_transactionNo,
            'transactionDate' => $this->_transactionDate,
            'descp' => $this->_description,
            'miscFee' => $this->_miscFee,
            'signature' => abs($this->_signature)
        );    
        
        $order->setKlikpaySignature(abs($this->_signature))->save();
        
        return $data;
    }
    
    public function getRedirectTimeout() {
        return (int) Mage::getStoreConfig('payment/klikpay/redirect_timeout');
    }

    public function getKlikpaySubmitUrl() {
        if (Mage::getStoreConfig('payment/klikpay/klikpay_redirect')) {
            return Mage::getStoreConfig('payment/klikpay/klikpay_redirect');    
        }
        
        return '';
    }
}
