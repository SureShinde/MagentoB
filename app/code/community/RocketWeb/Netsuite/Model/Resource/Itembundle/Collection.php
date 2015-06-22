<?php
/**
 * Description of RocketWeb_Netsuite_Model_Resource_Itembundle_Collection
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class RocketWeb_Netsuite_Model_Resource_Itembundle_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {
    public function _construct() {
        parent::_construct();
        
        $this->_init('rocketweb_netsuite/itembundle');
    }
}
