<?php
class RocketWeb_Netsuite_Model_Changelog extends Mage_Core_Model_Abstract {
    const PRODUCT_CHANGE = 'product_change';
    const PRODUCT_NEW = 'product_new';
    const PRODUCT_DELETED = 'product_deleted';
    const PRODUCT_DISABLED = 'product_disabled';
    const STOCK_ADJUSTMENT = 'stock_adjustment';

    public function _construct()
    {
        parent::_construct();
        $this->_init('rocketweb_netsuite/changelog');
    }
}