<?php
/**
 * Description of Phpro_Stockmonitor_Model_Resource_Overview
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Phpro_Stockmonitor_Model_Mysql4_Overview extends Mage_Core_Model_Mysql4_Abstract {
    public function _construct() {
        $this->_init('stockmonitor/overview', 'item_id');
    }
}
