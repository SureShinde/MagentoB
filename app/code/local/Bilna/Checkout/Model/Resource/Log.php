<?php
class Bilna_Checkout_Model_Resource_Log extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('bilna_checkout/log', 'id');
    }
}
