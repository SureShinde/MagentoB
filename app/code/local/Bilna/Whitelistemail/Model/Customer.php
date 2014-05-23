<?php
/**
 * Description of Bilna_Whitelistemail_Model_Customer
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Whitelistemail_Model_Customer extends Mage_Core_Model_Abstract {
    protected function _construct() {
        $this->_init('whitelistemail/customer');
    }
}
