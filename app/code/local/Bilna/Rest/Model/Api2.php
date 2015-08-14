<?php
/**
 * Description of Bilna_Rest_Model_Api2
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Rest_Model_Api2 extends Mage_Api2_Model_Resource {
    const DEFAULT_STORE_ID = 1;
    
    protected function _getStore() {
        return Mage::getModel('core/store')->load(self::DEFAULT_STORE_ID);
    }
}
