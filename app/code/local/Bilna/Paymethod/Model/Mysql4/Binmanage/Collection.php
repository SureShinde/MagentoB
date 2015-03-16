<?php
/**
 * Description of Bilna_Paymethod_Model_Mysql4_Binmanage_Collection
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Paymethod_Model_Mysql4_Binmanage_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {
    protected function _construct() {
        $this->_init('paymethod/binmanage');
    }
}
