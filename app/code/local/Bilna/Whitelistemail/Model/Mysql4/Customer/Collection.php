<?php
/**
 * Description of Bilna_Whitelistemail_Model_Resource_Customer_Collection
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Whitelistemail_Model_Mysql4_Customer_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {
    protected function _construct() {
        $this->_init('whitelistemail/customer');
    }
}
