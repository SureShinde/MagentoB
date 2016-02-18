<?php

class Bilna_Pricevalidation_Model_Mysql4_Log extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('bilna_pricevalidation/log', 'log_id');
    }
}
