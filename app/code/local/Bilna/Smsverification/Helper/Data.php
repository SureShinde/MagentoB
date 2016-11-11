<?php
class Bilna_Smsverification_Helper_Data extends Mage_Core_Helper_Abstract {
    public function validateMobileNumber($mobileNumber) {
        if(trim($mobileNumber) != ""){
            $mobileNumber = str_replace(array("+","-",".","(",")"," "), "", $mobileNumber);
            if (!preg_match ('/^[0-9]{'.Mage::getStoreConfig('bilna/smsverification/min_msisdn').','.Mage::getStoreConfig('bilna/smsverification/max_msisdn').'}$/', $mobileNumber)) {
                throw Mage::throwException("Invalid Mobile Number");
            }
        }
        $mobileNumber = substr($mobileNumber,0,1) == "0" ? "62".substr($mobileNumber,1) : $mobileNumber;
        return $mobileNumber;
    }

}
