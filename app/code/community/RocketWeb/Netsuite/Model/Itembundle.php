<?php
/**
 * Description of RocketWeb_Netsuite_Model_Itembundle
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class RocketWeb_Netsuite_Model_Itembundle extends Mage_Core_Model_Abstract {
    public function _construct() {
        parent::_construct();
        
        $this->_init('rocketweb_netsuite/itembundle');
    }
}
