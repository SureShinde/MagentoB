<?php

class Bilna_Promo_Model_Mysql4_Gimmickeventapplicant extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the id refers to the key field in your database table.
        $this->_init('bilnapromo/gimmickeventapplicant', 'id');
    }
}