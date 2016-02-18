<?php
/**
 * Description of Bilna_Visa_Model_Mysql4_Bincode
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Visa_Model_Mysql4_Bincode extends Mage_Core_Model_Mysql4_Abstract {
    public function _construct() { 
        // Note that the id refers to the key field in your database table.
        $this->_init('visa/bincode', 'id');
    }
}