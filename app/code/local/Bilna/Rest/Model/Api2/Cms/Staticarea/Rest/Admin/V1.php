<?php
/**
 * Description of Bilna_Rest_Model_Api2_Cms_Staticarea_Rest_Admin_V1
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Rest_Model_Api2_Cms_Staticarea_Rest_Admin_V1 extends Bilna_Rest_Model_Api2_Cms_Staticarea_Rest {
    protected function _retrieve() {
        $_identifier = $this->getRequest()->getParam('identifier');
        $_staticarea = Mage::getModel('staticarea/manage')->load($_identifier, 'block_id');
        
        if (!$_staticarea->getId()) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        
        $_result = $_staticarea->getData();
        $_result['contents'] = $this->_getStaticareaContents($_staticarea);
        
        return $_result;
    }
}
