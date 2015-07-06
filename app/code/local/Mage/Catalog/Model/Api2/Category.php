<?php
/**
 * Description of Mage_Catalog_Model_Api2_Category
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Mage_Catalog_Model_Api2_Category extends Mage_Api2_Model_Resource {
    const DEFAULT_STORE_ID = 1;
    
    protected function _getStore() {
        return Mage::getModel('core/store')->load(self::DEFAULT_STORE_ID);
    }
}
