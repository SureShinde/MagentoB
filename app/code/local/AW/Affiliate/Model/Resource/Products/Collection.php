<?php

class AW_Affiliate_Model_Resource_Products_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{

    public function _construct()
    {
        parent::_construct();
        $this->_init('awaffiliate/products');
    }
}
