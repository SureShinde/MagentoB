<?php
/**
 * Description of Bilna_Visa_Model_Bincode
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Visa_Model_Bincode extends Mage_Core_Model_Abstract {
    public function _construct() {
        parent::_construct();
        $this->_init('visa/bincode');
    }
}