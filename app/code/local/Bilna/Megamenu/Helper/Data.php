<?php
/**
 * Description of Bilna_Megamenu_Helper_Data
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Megamenu_Helper_Data extends Mage_Catalog_Helper_Category {
    public function getCurrentCategoryFrontend() {
        if (Mage::registry('current_category')) {
            return Mage::registry('current_category')->getId();
        }
        
        return false;
    }
    
    public function getCurrentMainCategory() {
        $mainCategory = array ('mom-baby', 'home', 'daily-deals');
        $result = '';
        
        if ($categoryId = $this->getCurrentCategoryFrontend()) {
            if (in_array($this->getCategoryName($categoryId), $mainCategory)) {
                $result = $this->getCategoryName($categoryId);
            }
            
            if (in_array($this->getCategoryName(Mage::getModel('catalog/category')->load($categoryId)->getParentId()), $mainCategory)) {
                $result = $this->getCategoryName(Mage::getModel('catalog/category')->load($categoryId)->getParentId());
            }
            
            if (in_array($this->getCategoryName(Mage::getModel('catalog/category')->load(Mage::getModel('catalog/category')->load($categoryId)->getParentId())->getParentId()), $mainCategory)) {
                $result = $this->getCategoryName(Mage::getModel('catalog/category')->load(Mage::getModel('catalog/category')->load($categoryId)->getParentId())->getParentId());
            }
        }
        
        return $result;
    }
    
    public function getCategoryName($categoryId) {
        return strtolower(str_replace(array (' & ', ' ', '!'), array ('-', '-', ''), Mage::getModel('catalog/category')->load($categoryId)->getName()));
    }
    
    public function replaceCategoryName($categoryName) {
        return str_replace('&', '<br/>&', $categoryName);
    }
}
