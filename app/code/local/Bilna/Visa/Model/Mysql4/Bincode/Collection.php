<?php
/**
 * Description of Bilna_Visa_Model_Mysql4_Bincode_Collection
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Visa_Model_Mysql4_Bincode_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {
    public function _construct() {
        parent::_construct();
        $this->_init('visa/bincode');
    }
}
