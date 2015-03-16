<?php
/**
 * Description of Bilna_Paymethod_Model_Mysql4_Binmanage
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Paymethod_Model_Mysql4_Binmanage extends Mage_Core_Model_Mysql4_Abstract {
    protected function _construct() {
        $this->_init('paymethod/binmanage', 'id');
    }
}