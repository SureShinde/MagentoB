<?php
class RocketWeb_Netsuite_Model_Resource_Changelog extends Mage_Core_Model_Mysql4_Abstract {

    public function _construct()
    {
        $this->_init('rocketweb_netsuite/changelog', 'id');
    }

}