<?php

class Bilna_Anzcc_Model_Bincode extends Mage_Core_Model_Abstract
{
    public function _construct()
    {    
        parent::_construct();
        $this->_init('anzcc/bincode');
    }
}