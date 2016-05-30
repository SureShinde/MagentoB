<?php
class Bilna_Checkout_Model_ActiveCoupon extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('bilna_checkout/activeCoupon');
    }
}
