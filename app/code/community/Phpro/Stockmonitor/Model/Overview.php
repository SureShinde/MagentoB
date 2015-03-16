<?php
/**
 * Description of Phpro_Stockmonitor_Model_Overview
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Phpro_Stockmonitor_Model_Overview extends Mage_Core_Model_Abstract {
    protected function _construct() {
        parent::_construct();
        
        $this->_init('stockmonitor/overview');
    }
}
