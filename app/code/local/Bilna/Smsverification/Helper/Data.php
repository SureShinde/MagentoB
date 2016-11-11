<?php
class Bilna_Smsverification_Helper_Data extends Mage_Core_Helper_Abstract {
    public function validateMobileNumber($mobileNumber) {
        if(trim($mobileNumber) != ""){
            $mobileNumber = str_replace(array("+","-",".","(",")"," "), "", $mobileNumber);
            if (!preg_match ('/^[0-9]*$/', $mobileNumber)) {
                throw Mage::throwException("Invalid Mobile Number");
            }
            if ((strlen($mobileNumber) < Mage::getStoreConfig('bilna/smsverification/min_msisdn')) || (strlen($mobileNumber) > Mage::getStoreConfig('bilna/smsverification/max_msisdn'))) {
                throw Mage::throwException("Invalid Mobile Number");
            }
        }
        $mobileNumber = substr($mobileNumber,0,1) == "0" ? "62".substr($mobileNumber,1) : $mobileNumber;
        return $mobileNumber;
    }

}
