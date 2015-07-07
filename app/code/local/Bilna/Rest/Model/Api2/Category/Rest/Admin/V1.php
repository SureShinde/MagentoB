<?php
/**
 * Description of Bilna_Rest_Model_Api2_Category_Rest_Admin_V1
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Rest_Model_Api2_Category_Rest_Admin_V1 extends Bilna_Rest_Model_Api2_Category_Rest {
    protected function _retrieve() {
        $categoryId = $this->getRequest()->getParam('id');
        $category = Mage::getModel('catalog/category')->load($categoryId);
        
        if (!($category->getId())) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        
        $categoryData = $this->_retrieveResponse($category);
        
        return $categoryData;
    }
    
    protected function _retrieveResponse($category) {
        $result = array (
            'id' => $category->getId(),
            'name' => $category->getName(),
            'is_active' => $category->getIsActive(),
            'description' => $category->getDescription(),
            'page_title' => $category->getMetaTitle(),
            'display_mode' => $category->getDisplayMode(),
            'cms_block' => $category->getLandingPage(),
            'page_layout' => $category->getPageLayout(),
        );
        
        return $result;
    }
}
