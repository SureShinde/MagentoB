<?php
class Bilna_Pricevalidation_Helper_Data extends Mage_Core_Helper_Abstract {
    CONST FORMBUILDER_SALT = 'B1LN4';

    public function encrypt($value) {
        return base64_encode($value . self::FORMBUILDER_SALT);
    }

    public function decrypt($value) {
        $value_dec = base64_decode($value);
        $result = substr($value_dec, 0, (strlen($value_dec) - strlen(self::FORMBUILDER_SALT)));

        return $result;
    }
}
