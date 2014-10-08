<?php
class Bilna_Veritrans_Model_PaymentMethod extends Mage_Payment_Model_Method_Banktransfer {
    protected $_code = 'veritrans';
    protected $_canAuthorize = true;

    public function authorize(Varien_Object $payment, $amount) {
        return true;
    }

    public function getOrderPlaceRedirectUrl() {
        return Mage::getUrl('veritrans/processing/redirect', array ('_secure' => true));
    }
}
