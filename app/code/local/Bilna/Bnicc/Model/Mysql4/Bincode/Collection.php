<?php
/**
 * Description of Bilna_Bnicc_Model_Mysql4_Bincode_Collection
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Bnicc_Model_Mysql4_Bincode_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {
    protected $_code = 'bnicc';
    
    public function _construct() {
        parent::_construct();
        $this->_init($this->_code . '/bincode');
    }
}
