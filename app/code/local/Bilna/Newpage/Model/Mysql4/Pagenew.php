<?php
class Bilna_Newpage_Model_Mysql4_Pagenew extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init("newpage/pagenew", "id");
    }
}