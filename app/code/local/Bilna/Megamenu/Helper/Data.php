<?php
/**
 * Description of Bilna_Megamenu_Helper_Data
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Megamenu_Helper_Data extends Mage_Catalog_Helper_Category {
    protected $_rootCategoryId = 2;
    
    public function getStoreCategories($sorted = false, $asCollection = false, $toLoad = true) {
        $parent = $this->_rootCategoryId;

        /**
         * Check if parent node of the store still exists
         */
        $category = Mage::getModel('catalog/category');

        /* @var $category Mage_Catalog_Model_Category */
        if (!$category->checkId($parent)) {
            if ($asCollection) {
                return new Varien_Data_Collection();
            }

            return array ();
        }

        $recursionLevel = max(0, (int) Mage::app()->getStore()->getConfig('catalog/navigation/max_depth'));
        $storeCategories = $category->getCategories($parent, $recursionLevel, $sorted, $asCollection, $toLoad);

        return $storeCategories;
    }
    
    public function getCategoryUrl($data) {
        $category = Mage::getModel('catalog/category')
            ->getCollection()
            ->addUrlRewriteToResult()
            ->addAttributeToFilter('entity_id', $data->getId())
            ->getFirstItem();
        
        return $category->getUrl();
    }
    
    public function getCurrentCategoryFrontend() {
        if (Mage::registry('current_category')) {
            return Mage::registry('current_category')->getId();
        }
        
        return false;
    }
    
    public function getMegamenuData() {
        $storeId = Mage::app()->getStore()->getStoreId();
        $directory = $this->getMegamenuDir();
        $filename = $directory . $storeId . ".json";
        $result = array ();
        
        if (file_exists($filename)) {
            $result = json_decode(file_get_contents($filename), true);
        }
        
        return $result;
    }
    
    public function getCurrentMainCategory() {
        $mainCategory = $this->getMainCategory();
        $result = '';
        
        if ($categoryId = $this->getCurrentCategoryFrontend()) {
            if (in_array(Mage::getModel('catalog/category')->load($categoryId)->getUrlKey(), $mainCategory)) {
                $result = $categoryId;
            }
            
            if (in_array($this->getUrlKey(Mage::getModel('catalog/category')->load($categoryId)->getParentId()), $mainCategory)) {
                $result = Mage::getModel('catalog/category')->load($categoryId)->getParentId();
            }
            
            if (in_array($this->getUrlKey(Mage::getModel('catalog/category')->load(Mage::getModel('catalog/category')->load($categoryId)->getParentId())->getParentId()), $mainCategory)) {
                $result = Mage::getModel('catalog/category')->load(Mage::getModel('catalog/category')->load($categoryId)->getParentId())->getParentId();
            }
        }
        
        return $result;
    }
    
    protected function getMainCategory() {
        $categories = $this->getStoreCategories();
        $result = array ();
        
        if (count($categories) > 0) {
            foreach ($categories as $category) {
                $categoryData = Mage::getModel('catalog/category')->load($category->getId());
                $result[] = $categoryData->getUrlKey();
            }
        }
        
        return $result;
    }
    
    public function getUrlKey($categoryId) {
        return Mage::getModel('catalog/category')->load($categoryId)->getUrlKey();
    }
    
    public function getCategoryName($categoryId) {
        return strtolower(str_replace(array (' & ', ' ', '!'), array ('-', '-', ''), Mage::getModel('catalog/category')->load($categoryId)->getName()));
    }

    public function replaceCategoryName($categoryName) {
        return str_replace('&', '<br/>&', $categoryName);
    }
    
    public function getCategoryClass($categoryId) {
        $search = array ('-', '_');
        $replace = '';
        
        return str_replace($search, $replace, $this->getUrlKey($categoryId));
    }
    
    public function getMegamenuDir() {
        return Mage::getBaseDir() . "/files/megamenu/";
    }
}
