<?php
/**
 * Description of Bilna_Rest_Model_Api2_Productfeatured_Rest_Admin_V1
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Rest_Model_Api2_Productfeatured_Rest_Admin_V1 extends Bilna_Rest_Model_Api2_Productfeatured_Rest {
    protected $_blockId = null;
    protected $_categoryId = null;
    protected $_block = null;
    protected $_collection = null;

    protected function _retrieve() {
        $this->_blockId = $this->getRequest()->getParam('id');
        $this->_categoryId = $this->getRequest()->getParam('category_id');
        $this->_block = Mage::getModel('awfeatured/blocks')->loadByBlockId($id, $this->_getStore());
        
        if (!$this->_block) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        
        if ($this->_canDisplay() && $this->_getProductsCollection()->getSize() && !$this->_getIsEmpty()) {
            $_helper = Mage::helper('catalog/output');
            $_imageQsize = 152; // Maximal image width\height value, pixels
            $_onSaleHelper = Mage::helper('awfeatured/onsale');
            $_abstractBlock = Mage::helper('awfeatured')->getAbstractProductBlock();
        }
    }
    
    protected function _canDisplay() {
        if ($this->_block) {
            if (!$this->_block->getIsActive()) {
                return false;
            }
            
            return true;
        }
        
        return false;
    }
    
    protected function _getProductsCollection() {
        if (is_null($this->_collection) && !is_null($this->_block->getAutomationType())) {
            switch ($this->_block->getAutomationType()) {
                case AW_Featured_Model_Source_Automation::NONE:
                    $this->_collection = $this->_getCollectionForIds();
                    $automationData = $this->_block->getAutomationData();
                    $productSortingType = $automationData['product_sorting_type'];
                    
                    if ($productSortingType == AW_Featured_Model_Source_Automation_Productsort::RANDOM) {
                        $this->_collection = $this->_getRandomProductsCollection($this->_collection);
                    }
                    elseif ($productSortingType == AW_Featured_Model_Source_Automation_Productsort::OLDFIRST) {
                        $this->_collection->getSelect()->order('entity_id asc');
                    }
                    else {
                        $this->_collection->getSelect()->order('entity_id desc');
                    }
                    
                    break;
                    
                case AW_Featured_Model_Source_Automation::RANDOM:
                    $this->_collection = $this->_getRandomProductsCollection();
                    break;
                
                case AW_Featured_Model_Source_Automation::TOPSELLERS:
                    $this->_collection = $this->_getTopSellersCollection();
                    break;
                
                case AW_Featured_Model_Source_Automation::TOPRATED:
                    $this->_collection = $this->_getTopRatedCollection();
                    break;
                
                case AW_Featured_Model_Source_Automation::MOSTREVIEWED:
                    $this->_collection = $this->_getMostReviewedCollection();
                    break;
                
                case AW_Featured_Model_Source_Automation::RECENTLYADDED:
                    $this->_collection = $this->_getRecentlyAddedCollection();
                    break;
                
                case AW_Featured_Model_Source_Automation::CURRENTCATEGORY:
                    $this->_collection = $this->_getCurrentCategoryCollection();
                    break;
                
                default:
                    $this->_collection = $this->_prepareCollection(Mage::getModel('awfeatured/product_collection'));
                    $this->_setIsEmpty(true);
                    break;
            }
            
            $this->_collection->addMinimalPrice();
            $this->_collection->joinOveralRating();
            $this->_collection->joinReviewsCount();
            
            $attr = array (
                'name', 'short_description', 'small_image', 'thumbnail', 'image',
                'msrp', 'msrp_enabled', 'msrp_display_actual_price_type', 'aw_os_category_display',
                'aw_os_category_position', 'aw_os_category_image', 'aw_os_category_image_path', 'aw_os_category_text', 'news_from_date', 'news_to_date'
            );
            $this->_collection->addAttributeToSelect($attr);
            $this->_collection->getSelect()->limit($this->_getItemsPerRow());
        }
        
        return $this->_collection;
    }
    
    protected function _getCollectionForIds() {
        $_collection = $this->_prepareCollection(Mage::getModel('awfeatured/product_collection'));
        
        if ($this->_block->getAutomationData()) {
            $_automationData = $this->_block->getAutomationData();
            $_products = isset ($_automationData['products']) ? @explode(',', $_automationData['products']) : array ();
            $_products = array_filter($_products, array (Mage::helper('awfeatured'), 'removeEmptyItems'));
            
            if (!$_products) {
                $this->_setIsEmpty(true);
            }
            else {
                $_collection->addAttributeToFilter('entity_id', $_products);
            }
            
            $_collection->getSelect()->joinLeft(
                array ('pi' => $_collection->getTable('awfeatured/productimages')),
                '(pi.product_id = e.entity_id) AND (pi.block_id = ' . $this->_block->getData('id') . ')',
                array ('image_id')
            );
        }
        
        return $_collection;
    }
    
    protected function _prepareCollection($_collection) {
        $_visibility = array (Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH, Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG);
        $_collection->addAttributeToFilter('visibility', $_visibility)->addAttributeToFilter('status', array ('in' => Mage::getSingleton('catalog/product_status')->getVisibleStatusIds()));
        
        if (!$this->_getShowOutOfStock()) {
            Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($_collection);
        }
        
        $_collection->addUrlRewrites()->addStoreFilter($this->_getStore()->getId())->groupByAttribute('entity_id');
        
        return $_collection;
    }
    
    protected function _getShowOutOfStock() {
        $_show = true;
        
        if (($_ciHelper = Mage::helper('cataloginventory')) && method_exists($_ciHelper, 'isShowOutOfStock')) {
            $_show = $_ciHelper->isShowOutOfStock();
        }
        
        return $_show;
    }
    
    protected function _getRandomProductsCollection($collection = null) {
        $_collection = $collection;
        
        if (null === $collection) {
            $_collection = $this->_prepareCollection(Mage::getModel('awfeatured/product_collection'));
        }
        
        $_collection->addMinimalPrice();
        
        if (null === $collection) {
            $this->_addCategoriesFilter($_collection);
            $_automationData = $this->getAFPBlock()->getAutomationData();
            $limit = isset ($_automationData['quantity_limit']) ? $_automationData['quantity_limit'] : 10;
            $ids = $_collection->getAllIds();
            $newArr = array ();
            
            if ($limit > count($ids)) {
                $limit = count($ids);
            }
            
            $randomPositions = (array) array_rand($ids, $limit);
            
            foreach ($randomPositions as $value) {
                $newArr[] = $ids[$value];
            }
            
            $ids = $newArr;
            $_collection->addFieldToFilter('entity_id', array ('in' => array ($ids)));
        }

        $_collection->getSelect()->order(new Zend_Db_Expr('RAND()'));
        
        if (null === $collection) {
            $this->_postprocessCollection($_collection);
        }
        
        return $_collection;
    }
    
    protected function _getTopSellersCollection($collection = null) {
        if (null === $collection) {
            $collection = $this->_prepareCollection(Mage::getModel('awfeatured/product_collection'));
            $this->_addCategoriesFilter($collection);
        }
        
        $collection->addOrderedQty()->sortByOrderedQty();
        $this->_postprocessCollection($collection);
        
        return $collection;
    }
    
    protected function _getTopRatedCollection($collection = null) {
        $_collection = $collection;
        
        if (null === $_collection) {
            $_collection = $this->_prepareCollection(Mage::getModel('awfeatured/product_collection'));
        }
        
        if (null === $collection) {
            $this->_addCategoriesFilter($_collection);
        }
        
        $_collection->joinOveralRating();
        $_collection->sortByRating();
        
        if (null === $collection) {
            $this->_postprocessCollection($_collection);
        }
        
        return $_collection;
    }
    
    protected function _getMostReviewedCollection($collection = null) {
        $_collection = $collection;
        
        if (null === $_collection) {
            $_collection = $this->_prepareCollection(Mage::getModel('awfeatured/product_collection'));
        }
        
        if (null === $collection) {
            $this->_addCategoriesFilter($_collection);
        }
        
        $_collection->joinReviewsCount();
        $_collection->sortByReviewsCount();
        
        if (null === $collection) {
            $this->_postprocessCollection($_collection);
        }
        
        return $_collection;
    }
    
    protected function _getRecentlyAddedCollection($collection = null) {
        $_collection = $collection;
        
        if (null === $_collection) {
            $_collection = $this->_prepareCollection(Mage::getModel('awfeatured/product_collection'));
        }
        
        if (null === $collection) {
            $this->_addCategoriesFilter($_collection);
        }
        
        $_collection->addAttributeToSort('created_at', 'desc');
        
        if (null === $collection) {
            $this->_postprocessCollection($_collection);
        }
        
        return $_collection;
    }
    
    protected function _getCurrentCategoryCollection($collection = null) {
        $_collection = $collection;
        
        if (null === $_collection) {
            $_collection = $this->_prepareCollection(Mage::getModel('awfeatured/product_collection'));
        }
        
        if ($this->_categoryId) {
            $_collection->addCategoriesFilter($this->_categoryId, true);
            
            switch ($this->_getAFPBlockAutomationData('current_category_type')) {
                case AW_Featured_Model_Source_Automation_Currentcategory::RANDOM:
                    $this->_getRandomProductsCollection($_collection);
                    break;
                
                case AW_Featured_Model_Source_Automation_Currentcategory::TOPSELLERS:
                    $this->_getTopSellersCollection($_collection);
                    break;
                
                case AW_Featured_Model_Source_Automation_Currentcategory::TOPRATED:
                    $this->_getTopRatedCollection($_collection);
                    break;
                
                case AW_Featured_Model_Source_Automation_Currentcategory::MOSTREVIEWED:
                    $this->_getMostReviewedCollection($_collection);
                    break;
                
                case AW_Featured_Model_Source_Automation_Currentcategory::RECENTLYADDED:
                default:
                    $this->_getRecentlyAddedCollection($_collection);
                    break;
            }
        }
        else {
            $this->_setIsEmpty(true);
        }
        
        if (null === $collection) {
            $this->_postprocessCollection($_collection);
        }
        
        return $_collection;
    }
    
    protected function _addCategoriesFilter($collection) {
        $_automationData = $this->_block->getAutomationData();
        $_categories = isset($_automationData['categories']) ? @explode(',', $_automationData['categories']) : array ();
        $_categories = array_filter($_categories, array (Mage::helper('awfeatured'), 'removeEmptyItems'));
        
        if (!$_categories) {
            $this->_setIsEmpty(true);
        }
        else {
            $collection->addCategoriesFilter($_categories);
        }
        
        return $collection;
    }
    
    protected function _postprocessCollection($collection) {
        $_automationData = $this->_block->getAutomationData();
        $_pSize = isset ($_automationData['quantity_limit']) ? $_automationData['quantity_limit'] : 10;
        $collection->setPageSize($_pSize);
        $collection->setCurPage(1);
        
        return $collection;
    }
    
    protected function _getAFPBlockAutomationData($key = null) {
        $_value = null;
        
        if ($this->_block) {
            $_automationData = $this->_block->getAutomationData();
            $_value = $_automationData;
            
            if (null !== $key) {
                $_value = isset ($_automationData[$key]) ? $_automationData[$key] : null;
            }
        }
        
        return $_value;
    }
    
    protected function _getAFPBlockTypeData($key = null) {
        $_value = null;
        
        if ($this->_block) {
            $_typeData = $this->_block->getTypeData();
            $_value = $_typeData;
            
            if (null !== $key) {
                $_value = isset ($_typeData[$key]) ? $_typeData[$key] : null;
            }
        }
        
        return $_value;
    }
    
    protected function _getItemsPerRow() {
        $_ppr = 1;
        
        if ($_ipg = $this->_getItemPerGrid()) {
            $_ppr = $_ipg;
        }
        else {
            if ($this->_getAFPBlockTypeData('productsinrow') && $this->_getAFPBlockTypeData('productsinrow') > 0) {
                $_ppr = $this->_getAFPBlockTypeData('productsinrow');
            }
        }
        
        return $_ppr;
    }
    
    protected function _getItemPerGrid() {
        $result = false;
        
        if ($limit = Mage::getStoreConfig('awfeatured/configuration/item_per_grid')) {
            $result = (int) $limit;
        }
        
        return $result;
    }
    
    protected function _setIsEmpty($flag) {
        return Mage::getModel('adminhtml/report_item')->setIsEmpty($flag);
    }
    
    protected function _getIsEmpty() {
        return Mage::getModel('adminhtml/report_item')->getIsEmpty();
    }
}
