<?php
/**
 * Description of Bilna_Routes_Model_Api2_Staticpage_Rest
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Routes_Model_Api2_Staticpage_Rest extends Bilna_Routes_Model_Api2_Staticpage {
    protected function _retrieve() {
        $path = $this->getRequest()->getParam('path');
        $modelCmsPage = Mage::getModel('cms/page');
        $pageId = $modelCmsPage->checkIdentifier($path, 1);
        
        if (!$pageId) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        
        return $modelCmsPage->load($pageId)->getData();
    }
}