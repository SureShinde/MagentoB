<?php
/**
 * Description of Bilna_Rest_Model_Api2_Productrelated_Rest
 *
 * @author Bilna Development Team <development@bilna.com>
 */

abstract class Bilna_Rest_Model_Api2_Productrelated_Rest extends Bilna_Rest_Model_Api2_Productrelated {
    protected $_data = array ();
    protected $_name = null;
    protected $_customerGroupId = null;
    protected $_productId = null;
    protected $_product = null;

    protected $_blocks = null;
    protected $_canShow = null;
    protected $_collection = null;
    protected $_joinedAttributes;
    
    protected function _createValidator() {
        $this->_getParams();
        
        $validator = Mage::getModel('bilna_rest/api2_productrelated_validator_productrelated');
        
        if (!$validator->isValidData($this->_data)) {
            foreach ($validator->getErrors() as $error) {
                $this->_error($error, Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
            }
            
            $this->_critical(self::RESOURCE_DATA_PRE_VALIDATION_ERROR);
        }
    }

    protected function _getParams() {
        $this->_name = $this->getRequest()->getQuery('name');
        $this->_customerGroupId = $this->getRequest()->getQuery('customer_group_id');
        $this->_productId = $this->getRequest()->getQuery('product_id');
        $this->_data = array (
            'name' => $this->_name,
            'customer_group_id' => $this->_customerGroupId,
            'product_id' => $this->_productId,
        );
    }
    
    protected function _checkVersion() {
        if (Mage::helper('awautorelated')->checkVersion('1.4')) {
            return true;
        }
        else {
            $this->_critical(self::RESOURCE_INTERNAL_ERROR);
        }
    }

    protected function _getProduct() {
        $this->_product = Mage::getModel('catalog/product')->setStoreId($this->_getStore()->getId())->load($this->_productId);
    }

    protected function _getBlocks() {
        if ($this->_blocks === null) {
            $collection = Mage::getModel('awautorelated/blocks')->getCollection()
                ->addFieldToFilter('name', $this->_name)
                ->addStoreFilter($this->_getStore()->getId())
                ->addStatusFilter()
                ->addCustomerGroupFilter($this->_customerGroupId)
                ->addDateFilter()
                ->setPriorityOrder();
            
            $this->_blocks = $collection->getFirstItem();
        }
        
        return $this->_blocks;
    }
    
    protected function _getCollection() {
        if ($this->_canShow()) {
            if ($this->_collection === null) {
                $this->_initCollection();
                $this->_renderRelatedProductsFilters();
                $this->_postProcessCollection();
            }
            
            return $this->_collection;
        }
        
        return null;
    }
    
    protected function _canShow() {
        if ($this->_canShow === null) {
            $model = Mage::getModel('awautorelated/blocks_product_ruleviewed')->setWebsiteIds($this->_getStore()->getWebsite()->getId());
            $conditions = $this->_blocks->getCurrentlyViewed()->getConditions();
            
            if (isset ($conditions['viewed'])) {
                $model->getConditions()->loadArray($conditions, 'viewed');
                $match = $model->getMatchingProductIds();
                
                if (in_array($this->_productId, $match)) {
                    $this->_canShow = true;
                }
                else {
                    $this->_canShow = false;
                }
            }
            else {
                $this->_canShow = true;
            }
        }
        
        return $this->_canShow;
    }
    
    protected function _initCollection() {
        if ($this->_collection === null) {
            $this->_collection = Mage::getModel('awautorelated/product_collection')->addAttributeToSelect('*');

            $_visibility = array (
                Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
                Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG
            );

            $this->_collection->addAttributeToFilter('visibility', $_visibility)
                ->addAttributeToFilter('status', array ('in' => Mage::getSingleton('catalog/product_status')->getVisibleStatusIds()));

            if (!$this->_getShowOutOfStock()) {
                Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($this->_collection);
            }

            $this->_collection->addStoreFilter($this->_getStore()->getId())
                ->joinCategoriesByProduct($this->_getStore()->getId())
                ->groupByAttribute('entity_id');
        }
        
        return $this->_collection;
    }
    
    protected function _getShowOutOfStock() {
        return $this->_blocks->getRelatedProducts()->getShowOutOfStock();
    }
    
    protected function _renderRelatedProductsFilters() {
        $model = Mage::getModel('awautorelated/blocks_product_rulerelated')->setWebsiteIds($this->_getStore()->getWebsite()->getId());
        $conditions = $this->_blocks->getRelatedProducts()->getRelated();
        $gCondition = $this->_blocks->getRelatedProducts()->getGeneral();
        $limit = $this->_blocks->getRelatedProducts()->getProductQty();
        $mIds = array ();

        if (isset ($conditions['conditions']['related'])) {
            $model->getConditions()->loadArray($conditions['conditions'], 'related');
            $mIds = $model->getMatchingProductIds();

            if (empty ($mIds)) {
                unset ($this->_collection);
            }
            else {
                $mIds = array_diff($mIds, array ($this->_productId));
            }
        }

        if (!empty ($gCondition)) {
            $filteredIds = $this->_filterByAtts($this->_product, $gCondition, $mIds);
        }
        elseif (!empty ($mIds)) {
            $filteredIds = $mIds;
        }
        else {
            $filteredIds = $this->_collection->getAllIds();
        }

        if (!empty ($filteredIds)) {
            $filteredIds = array_diff($filteredIds, array ($this->_productId));
            $filteredIds = array_diff($filteredIds, Mage::helper('awautorelated')->getWishlistProductsIds());
            $filteredIds = array_diff($filteredIds, Mage::getSingleton('checkout/cart')->getProductIds());
            $filteredIds = array_intersect($filteredIds, $this->_collection->getAllIds());
            $itemsCount = count($filteredIds);
            
            if (!$itemsCount) {
                unset ($this->_collection);
            }
            
            $this->_preorderIds($filteredIds);
            $this->_initCollectionForIds($filteredIds);
            $this->_collection->setPageSize($limit);
            $this->_collection->setCurPage(1);
            $this->_orderRelatedProductsCollection($this->_collection);
        }
        else {
            unset ($this->_collection);
        }
    }
    
    protected function _filterByAtts(Mage_Catalog_Model_Product $currentProduct, $atts, $ids = null) {
        $this->_joinedAttributes = array ();
        $collection = $this->_collection;
        $rule = new AW_Autorelated_Model_Blocks_Rule();

        foreach ($atts as $at) {
            /**
             * collect category ids related to product
             * If category is anchor we should implode all of its subcategories as value
             * If it's not we should get only its id
             * If there is no category in product, get all categories product is in
             */
            if ($at['att'] == 'category_ids') {
                $category = $currentProduct->getCategory();
                
                if ($category instanceof Varien_Object) {
                    if ($category->getIsAnchor()) {
                        $value = $category->getAllChildren();
                    }
                    else {
                        $value = $category->getId();
                    }
                }
                else {
                    $value = implode(',', $currentProduct->getCategoryIds());
                    $value = !empty ($value) ? $value : null;
                }
            }
            else {
                $value = $currentProduct->getData($at['att']);
            }
            
            if (!$value) {
                $collection = null;
                
                return false;
            }
            
            $sql = $rule->prepareSqlForAtt($at['att'], $this->_joinedAttributes, $collection, $at['condition'], $value);
            
            if ($sql) {
                $collection->getSelect()->where($sql);
            }
        }
        
        if ($ids) {
            $collection->getSelect()->where('e.entity_id IN(' . implode(',', $ids) . ')');
        }
        
        $collection->getSelect()->group('e.entity_id');

        return $collection->getAllIds();
    }
    
    protected function _preorderIds(array $ids) {
        $relatedProductsOrder = $this->_getRelatedProductsOrder();
        
        if ($relatedProductsOrder['type'] == AW_Autorelated_Model_Source_Block_Common_Order::RANDOM) {
            shuffle($ids);
            $ids = array_values($ids);
        }
        
        return $ids;
    }
    
    protected function _getRelatedProductsOrder() {
        $rpOrder = array ('type' => AW_Autorelated_Model_Source_Block_Common_Order::NONE);
        
        if ($relatedProducts = $this->_blocks->getRelatedProducts()) {
            if (is_array($order = $relatedProducts->getData('order'))) {
                $rpOrder = $order;
            }
        }
        
        return $rpOrder;
    }
    
    protected function _initCollectionForIds(array $ids) {
        unset ($this->_collection);
        $this->_collection = Mage::getModel('awautorelated/product_collection')->addAttributeToSelect('*')
            ->addFilterByIds($ids)
            ->setStoreId($this->_getStore()->getId());
        
        return $this->_collection;
    }
    
    protected function _orderRelatedProductsCollection($collection) {
        /** @var $collection Mage_Catalog_Model_Resource_Product_Collection */
        $orderSettings = $this->_getRelatedProductsOrder();
        
        switch ($orderSettings['type']) {
            case AW_Autorelated_Model_Source_Block_Common_Order::RANDOM:
                $collection->getSelect()->order(new Zend_Db_Expr('RAND()'));
                break;
            case AW_Autorelated_Model_Source_Block_Common_Order::BY_ATTRIBUTE:
                $collection->addAttributeToSort($orderSettings['attribute'], $orderSettings['direction']);
                break;
        }
        
        $this->_collection = $collection;
    }
    
    protected function _postProcessCollection() {
        if ($this->_collection instanceof AW_Autorelated_Model_Product_Collection) {
            $this->_collection->setStoreId($this->_getStore()->getId())
                ->addUrlRewrites()
                ->addMinimalPrice()
                ->groupByAttribute('entity_id');
            
            if ($this->_getShowOutOfStock() && !Mage::helper('cataloginventory')->isShowOutOfStock()) {
                $fromPart = $this->_collection->getSelect()->getPart(Zend_Db_Select::FROM);
                
                if (isset ($fromPart['price_index']) && is_array($fromPart['price_index']) && isset ($fromPart['price_index']['joinType']) && $fromPart['price_index']['joinType'] === Zend_Db_Select::INNER_JOIN) {
                    $fromPart['price_index']['joinType'] = Zend_Db_Select::LEFT_JOIN;
                    $this->_collection->getSelect()->setPart(Zend_Db_Select::FROM, $fromPart);
                }
            }
        }
    }
}
