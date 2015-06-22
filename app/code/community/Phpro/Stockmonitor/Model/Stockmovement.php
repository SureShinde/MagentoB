<?php

class Phpro_Stockmonitor_Model_Stockmovement extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('stockmonitor/stockmovement');
        
    }
}