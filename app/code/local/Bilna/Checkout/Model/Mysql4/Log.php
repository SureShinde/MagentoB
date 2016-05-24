<?php
class Bilna_Checkout_Model_Mysql4_Log extends Mage_Core_Model_Mysql4_Abstract
{
    //protected $_isPkAutoIncrement = false;

    protected function _construct()
    {
        $this->_init('bilna_checkout/log', 'id');
    }
}
