<?php

class Phpro_Stockmonitor_Model_Mysql4_Stockmovement extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('stockmonitor/stockmovement', 'movement_id');
    }
}