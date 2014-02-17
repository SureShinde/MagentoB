<?php   
class Bilna_Veritrans_Block_Index extends Mage_Core_Block_Template {
    public function _getRedirectPage() {
        return Mage::getStoreConfig('payment/veritrans/url_redirection');
    }
    
    public function _getMerchantId() {
        return Mage::getStoreConfig('payment/veritrans/merchant_id');
    }
    
    public function _getOrderId() {
        return Mage::getSingleton('checkout/session')->getLastRealOrderId();
    }
    
    public function _getTokenVeritrans() {
        return Mage::getSingleton('checkout/session')->getBilnaVeritransResponse();
    }
    
    public function _getRedirectMessage() {
        $timeout = $this->_getRedirectTimeout() / 1000;
        
        return sprintf("This page will be redirected to Veritrans page in %d seconds.", (int) $timeout);
    }

    public function _getRedirectTimeout() {
        return (int) Mage::getStoreConfig('payment/veritrans/redirect_timeout') * 1000;
    }
}
