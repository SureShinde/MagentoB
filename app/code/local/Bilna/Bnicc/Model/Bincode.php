<?php
/**
 * Description of Bilna_Bnicc_Model_Bincode
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Bnicc_Model_Bincode extends Mage_Core_Model_Abstract {
    protected $_code = 'bnicc';
    
    public function _construct() {
        parent::_construct();
        $this->_init($this->_code . '/bincode');
    }
}