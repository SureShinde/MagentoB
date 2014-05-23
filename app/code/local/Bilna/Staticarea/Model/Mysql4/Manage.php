<?php
class Bilna_Staticarea_Model_Mysql4_Manage extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init("staticarea/manage", "id");
    }
}