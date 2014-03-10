<?php
class Bilna_Staticarea_Model_Mysql4_Staticarea extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init("staticarea/staticarea", "area_id");
    }
}