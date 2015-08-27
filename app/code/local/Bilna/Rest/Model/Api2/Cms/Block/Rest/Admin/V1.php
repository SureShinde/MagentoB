<?php
/**
 * Description of Bilna_Rest_Model_Api2_Cms_Block_Rest_Admin_V1
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Rest_Model_Api2_Cms_Block_Rest_Admin_V1 extends Bilna_Rest_Model_Api2_Cms_Block_Rest {
    protected function _retrieve() {
        $identifier = $this->getRequest()->getParam('identifier');
        $block = Mage::getModel('cms/block')->load($identifier)->getData();
        
        return $block;
    }
}
