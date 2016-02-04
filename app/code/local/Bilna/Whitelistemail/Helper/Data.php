<?php
/**
 * Bilna_Whitelistemail_Helper_Data
 * 
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Whitelistemail_Helper_Data extends Mage_Core_Helper_Abstract {
    public function getCustomerEncodeKey($customerId, $customerCode) {
        $encodeString = $customerId . "_" . $customerCode;
        $key = base64_encode($encodeString);
        
        return $key;
    }
    
    public function getCustomerDecodeKey($key) {
        $decode = base64_decode($key);
        $result = explode('_', $decode);
        
        return array (
            'customer_id' => $result[0],
            'code' => $result[1]
        );
    }
    
    public function getCustomerCode($length = 6) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        
        return substr(str_shuffle($chars), 0, $length);
    }
}
