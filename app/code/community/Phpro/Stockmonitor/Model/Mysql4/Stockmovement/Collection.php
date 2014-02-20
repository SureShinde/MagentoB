<?php

class Phpro_Stockmonitor_Model_Mysql4_Stockmovement_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('stockmonitor/stockmovement');
    }
	
    public function addAttributeToFilter($attribute, $condition = null)
    {
        $this->addFieldToFilter($attribute, $condition);
        return $this;
    }
}