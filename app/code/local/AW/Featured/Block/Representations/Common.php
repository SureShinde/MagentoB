<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Featured
 * @version    3.5.2
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Featured_Block_Representations_Common extends Mage_Core_Block_Template
{
    private $_awafpblock = null;
    private $_uniqueBlockId = null;
    /** @var $_collection AW_Featured_Model_Mysql4_Blocks_Collection */
    private $_collection = null;

    public function setAFPBlock($block)
    {
        $this->_awafpblock = $block;
        return $this;
    }

    protected function _getShowOutOfStock()
    {
        $_show = true;
        if (($_ciHelper = Mage::helper('cataloginventory')) && method_exists($_ciHelper, 'isShowOutOfStock')) {
            $_show = $_ciHelper->isShowOutOfStock();
        }
        return $_show;
    }

    protected function _prepareCollection($_collection)
    {
        $_visibility = array(
            Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
            Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG
        );
        $_collection->addAttributeToFilter('visibility', $_visibility)
            ->addAttributeToFilter(
                'status',
                array("in" => Mage::getSingleton("catalog/product_status")->getVisibleStatusIds())
            );
        if (!$this->_getShowOutOfStock()) {
            Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($_collection);
        }
        $_collection->addUrlRewrites()
            ->addStoreFilter(Mage::app()->getStore()->getId())
            ->groupByAttribute('entity_id')
        ;
        return $_collection;
    }

    protected function _addCategoriesFilter($collection)
    {
        $_automationData = $this->getAFPBlock()->getAutomationData();
        $_categories = isset($_automationData['categories']) ? @explode(',', $_automationData['categories']) : array();
        $_categories = array_filter($_categories, array(Mage::helper('awfeatured'), 'removeEmptyItems'));
        if (!$_categories) {
            $this->setIsEmpty(true);
        } else {
            $collection->addCategoriesFilter($_categories);
        }
        return $collection;
    }

    protected function _getCollectionForIds()
    {
        $_collection = $this->_prepareCollection(Mage::getModel('awfeatured/product_collection'));
        if ($this->getAFPBlock()->getAutomationData()) {
            $_automationData = $this->getAFPBlock()->getAutomationData();
            $_products = isset($_automationData['products']) ? @explode(',', $_automationData['products']) : array();
            $_products = array_filter($_products, array(Mage::helper('awfeatured'), 'removeEmptyItems'));
            if (!$_products) {
                $this->setIsEmpty(true);
            } else {
                $_collection->addAttributeToFilter('entity_id', $_products);
            }
            $_collection->getSelect()->joinLeft(
                array('pi' => $_collection->getTable('awfeatured/productimages')),
                '(pi.product_id = e.entity_id) AND (pi.block_id = ' . $this->getAFPBlock()->getData('id') . ')',
                array('image_id')
            );
        }
        return $_collection;
    }

    protected function _getTopSellersCollection($collection = null)
    {
        if (null === $collection) {
            $collection = $this->_prepareCollection(Mage::getModel('awfeatured/product_collection'));
            $this->_addCategoriesFilter($collection);
        }
        $collection
            ->addOrderedQty()
            ->sortByOrderedQty()
        ;
        $this->_postprocessCollection($collection);
        return $collection;
    }

    protected function _getRandomProductsCollection($collection = null)
    {
        $_collection = $collection;
        if (null === $collection) {
            $_collection = $this->_prepareCollection(Mage::getModel('awfeatured/product_collection'));
        }
        $_collection->addMinimalPrice();
        if (null === $collection) {
            $this->_addCategoriesFilter($_collection);
            $_automationData = $this->getAFPBlock()->getAutomationData();
            $limit = isset($_automationData['quantity_limit']) ? $_automationData['quantity_limit'] : 10;

            $ids = $_collection->getAllIds();

            $newArr = array();
            if ($limit > count($ids)) {
                $limit = count($ids);
            }
            $randomPositions = (array)array_rand($ids, $limit);
            foreach ($randomPositions as $value) {
                $newArr[] = $ids[$value];
            }
            $ids = $newArr;
            $_collection->addFieldToFilter('entity_id', array("in" => array($ids)));
        }

        $_collection->getSelect()->order(new Zend_Db_Expr('RAND()'));
        if (null === $collection) {
            $this->_postprocessCollection($_collection);
        }
        return $_collection;
    }

    protected function _getRecentlyAddedCollection($collection = null)
    {
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

    protected function _getTopRatedCollection($collection = null)
    {
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

    protected function _getMostReviewedCollection($collection = null)
    {
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

    protected function _getCurrentCategoryCollection($collection = null)
    {
        $_collection = $collection;
        if (null === $_collection) {
            $_collection = $this->_prepareCollection(Mage::getModel('awfeatured/product_collection'));
        }
        if (Mage::registry('current_category') && Mage::registry('current_category')->getId()) {
            $_collection->addCategoriesFilter(Mage::registry('current_category')->getId(), true);
            switch ($this->getAFPBlockAutomationData('current_category_type')) {
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
        } else {
            $this->setIsEmpty(true);
        }
        if (null === $collection) {
            $this->_postprocessCollection($_collection);
        }
        return $_collection;
    }

    protected function _postprocessCollection($collection)
    {
        $_automationData = $this->getAFPBlock()->getAutomationData();
        $_pSize = isset($_automationData['quantity_limit']) ? $_automationData['quantity_limit'] : 10;
        $collection->setPageSize($_pSize);
        $collection->setCurPage(1);
        return $collection;
    }

    public function getProductsCollection()
    {
        if (is_null($this->_collection) && !is_null($this->getAFPBlock()->getAutomationType())) {
            switch ($this->getAFPBlock()->getAutomationType()) {
                case AW_Featured_Model_Source_Automation::NONE:
                    $this->_collection = $this->_getCollectionForIds();
                    $automationData = $this->getAFPBlock()->getAutomationData();
                    $productSortingType = $automationData['product_sorting_type'];
                    if ($productSortingType == AW_Featured_Model_Source_Automation_Productsort::RANDOM) {
                        $this->_collection = $this->_getRandomProductsCollection($this->_collection);
                    } elseif ($productSortingType == AW_Featured_Model_Source_Automation_Productsort::OLDFIRST) {
                        $this->_collection->getSelect()->order('entity_id asc');
                    } else {
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
                    $this->setIsEmpty(true);
                    break;
            }
            $this->_collection->addMinimalPrice();
            $this->_collection->joinOveralRating();
            $this->_collection->joinReviewsCount();
            $attr = array(
                'name', 'short_description', 'small_image', 'thumbnail', 'image',
                'msrp', 'msrp_enabled', 'msrp_display_actual_price_type', 'aw_os_category_display',
                'aw_os_category_position', 'aw_os_category_image', 'aw_os_category_image_path', 'aw_os_category_text','news_from_date','news_to_date'
            );
            $this->_collection->addAttributeToSelect($attr);
        }
        return $this->_collection;
    }

    public function getReviewsUrl($product)
    {
        return Mage::getUrl(
            'review/product/list',
            array(
                'id' => $product->getId(),
                'category' => $product->getCategoryId()
            )
        );
    }

    public function getAFPBlock()
    {
        return $this->_awafpblock;
    }

    public function getAFPBlockAutomationData($key = null)
    {
        $_value = null;
        if ($this->getAFPBlock()) {
            $_automationData = $this->getAFPBlock()->getAutomationData();
            $_value = $_automationData;
            if (null !== $key) {
                $_value = isset($_automationData[$key]) ? $_automationData[$key] : null;
            }
        }
        return $_value;
    }

    public function getAFPBlockTypeData($key = null)
    {
        $_value = null;
        if ($this->getAFPBlock()) {
            $_typeData = $this->getAFPBlock()->getTypeData();
            $_value = $_typeData;
            if (null !== $key) {
                $_value = isset($_typeData[$key]) ? $_typeData[$key] : null;
            }
        }
        return $_value;
    }

    public function canDisplay()
    {
        if ($this->getAFPBlock()) {
            $currentStoreId = Mage::app()->getStore()->getId();
            if (array_intersect($this->getAFPBlock()->getStore(), array(0, $currentStoreId)) == array()) {
                return false;
            }
            if (!$this->getAFPBlock()->getIsActive()) {
                return false;
            }
            return true;
        }
        return false;
    }

    public function getUniqueBlockId()
    {
        if (is_null($this->_uniqueBlockId)) {
            $this->_uniqueBlockId = uniqid('awafpblock');
        }
        return $this->_uniqueBlockId;
    }

    public function stripTags($data, $allowableTags = null, $allowHtmlEntities = false)
    {
        if (Mage::helper('awfeatured')->checkVersion('1.4.1.1')) {
            return parent::stripTags($data, $allowableTags, $allowHtmlEntities);
        } else {
            return $this->escapeHtml($data);
        }
    }
}
