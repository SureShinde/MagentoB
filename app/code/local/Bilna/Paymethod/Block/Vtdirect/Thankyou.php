<?php
/**
 * Description of Bilna_Paymethod_Block_Vtdirect_Thankyou
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Paymethod_Block_Vtdirect_Thankyou extends Mage_Checkout_Block_Onepage_Success {
    public function getThreedSecure() {
        return Mage::registry('threedsecure');
    }
    
    public function getResponseCharge() {
        return Mage::registry('response_charge');
    }
    
    public function getChargeTimeoutMessage() {
        return Mage::getStoreConfig('payment/vtdirect/charge_timeout_message');
    }
    
    public function getDefaultResponseMessage($status, $message) {
        $result = '';
        
        if ($message) {
            $result = $message;
        }
        else {
            if ($status == 'success') {
                $result = Mage::getStoreConfig('payment/vtdirect/default_response_message_success');
            }
            else if ($status == 'failure') {
                $result = Mage::getStoreConfig('payment/vtdirect/default_response_message_failure');
            }
            else {
                $result = Mage::getStoreConfig('payment/vtdirect/charge_timeout_message');
            }
        }
        
        return $result;
    }
}
