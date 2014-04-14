<?php
class RocketWeb_Netsuite_Helper_Mapper_Product_Config extends Mage_Core_Helper_Abstract {
    const CATEGORY_CONVERTOR = '__special_categories';

    public function getConvertorKeysAndNames() {
        return array(
            self::CATEGORY_CONVERTOR => '**Category IDs'
        );
    }
}