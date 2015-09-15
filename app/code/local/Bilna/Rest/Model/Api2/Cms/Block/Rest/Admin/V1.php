<?php
/**
 * Description of Bilna_Rest_Model_Api2_Cms_Block_Rest_Admin_V1
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Rest_Model_Api2_Cms_Block_Rest_Admin_V1 extends Bilna_Rest_Model_Api2_Cms_Block_Rest {
    protected function _retrieve() {
        $_identifier = $this->getRequest()->getParam('identifier');
        $_block = Mage::getModel('cms/block')->load($_identifier)->getData();
        
        if (count($_block) == 0) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        
        return $_block;
    }
}
