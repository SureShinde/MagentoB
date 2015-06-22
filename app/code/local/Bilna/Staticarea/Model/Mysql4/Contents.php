<?php
class Bilna_Staticarea_Model_Mysql4_Contents extends Mage_Core_Model_Mysql4_Abstract {
    const DATE_FORMAT = 'Y-m-d';

    public function _construct() {
        $this->_init('staticarea/contents', 'id');
    }
}