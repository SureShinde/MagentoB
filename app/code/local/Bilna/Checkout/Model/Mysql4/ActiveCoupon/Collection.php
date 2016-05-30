<?php
class Bilna_Checkout_Model_Mysql4_ActiveCoupon_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('bilna_checkout/activeCoupon');
    }
}
