<?php
class RocketWeb_Netsuite_Model_Resource_Changelog_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {
    public function _construct()
    {
        parent::_construct();
        $this->_init('rocketweb_netsuite/changelog');
    }
}