<?php
class Bilna_Pricevalidation_Model_Mysql4_Form extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('bilna_pricevalidation/form', 'profile_id');
    }
}
