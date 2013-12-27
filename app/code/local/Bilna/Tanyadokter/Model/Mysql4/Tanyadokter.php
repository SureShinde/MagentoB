<?php

class Bilna_Tanyadokter_Model_Mysql4_Tanyadokter extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('bilna_tanyadokter/tanyadokter', 'id');
    }
}