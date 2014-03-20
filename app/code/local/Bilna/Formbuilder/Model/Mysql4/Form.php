<?php

class Bilna_Formbuilder_Model_Mysql4_Form extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('bilna_formbuilder/form', 'form_id');
    }
}
