<?php
/**
 * Description of Bilna_Formbuilder_Model_Mysql4_Flat
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Formbuilder_Model_Mysql4_Flat extends Mage_Core_Model_Mysql4_Abstract {
    public function _construct() {
        $this->_init('bilna_formbuilder/flat', 'id');
    }
}
