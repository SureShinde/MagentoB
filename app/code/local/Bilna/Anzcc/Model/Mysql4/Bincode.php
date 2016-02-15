<?php

class Bilna_Anzcc_Model_Mysql4_Bincode extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the id refers to the key field in your database table.
        $this->_init('anzcc/bincode', 'id');
    }
}