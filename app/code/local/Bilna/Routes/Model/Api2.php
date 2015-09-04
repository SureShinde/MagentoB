<?php
/**
 * Description of Bilna_Routes_Model_Api2
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Routes_Model_Api2 extends Mage_Api2_Model_Resource {
    const DEFAULT_STORE_ID = 1;
    
    protected $_data = array ();
    
    public function __construct() {
        Mage::app()->getStore()->setStoreId(self::DEFAULT_STORE_ID);
    }
    
    protected function _getStore() {
        return Mage::app()->getStore();
    }
}
