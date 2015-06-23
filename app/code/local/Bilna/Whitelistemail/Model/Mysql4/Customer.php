<?php
/**
 * Description of Bilna_Whitelistemail_Model_Mysql4_Customer
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Whitelistemail_Model_Mysql4_Customer extends Mage_Core_Model_Mysql4_Abstract {
    protected function _construct() {
        $this->_init('whitelistemail/customer', 'entity_id');
    }
}
