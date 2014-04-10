<?php

class Bilna_Formbuilder_Model_Mysql4_Form_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        $this->_init('bilna_formbuilder/form');
    }
}