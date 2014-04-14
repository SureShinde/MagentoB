<?php
/**
 * @author  Bilna Development Team <development@bilna.com>
 */

class AW_Advancedreports_Model_Mysql4_Premiumrate_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {
    protected function _construct() {
        parent::_construct();
        
        $this->_init('advancedreports/premiumrate');
    }    
}
