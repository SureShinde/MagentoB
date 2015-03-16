<?php

class Bilna_Promo_Model_Mysql4_Giftvoucherapplicant_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('bilnapromo/giftvoucherapplicant');
    }
}