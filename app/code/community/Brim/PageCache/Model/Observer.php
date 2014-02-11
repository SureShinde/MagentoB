<?php
/**
 * Brim LLC Commercial Extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Brim LLC Commercial Extension License
 * that is bundled with this package in the file Brim-LLC-Magento-License.pdf.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.brimllc.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to support@brimllc.com so we can send you a copy immediately.
 *
 * @category   Brim
 * @package    Brim_PageCache
 * @copyright  Copyright (c) 2011-2012 Brim LLC
 * @license    http://ecommerce.brimllc.com/license
 */

/**
 * Observes magento events in order to cache and serve cached pages.
 */
class Brim_PageCache_Model_Observer extends Varien_Event_Observer
{
    /**
     * @var bool
     */
    protected $_cachePageCalled = false;

    /**
     * @var Brim_PageCache_Model_Engine
     */
    protected $_engine = null;

    /**
     * @return Brim_PageCache_Model_Engine|Mage_Core_Model_Abstract
     */
    public function getEngine() {
        if ($this->_engine == null) {
            $this->_engine = Mage::getSingleton('brim_pagecache/engine');
        }
        return $this->_engine;
    }

    /**
     * Handles logic for caching pages.  Currently checks the root block for the
     * "CachePageFlag".  Pages are only cached if the user is not logged in and does
     * not have items in their cart.
     *
     * Observes: controller_front_send_response_before
     *  controller_front_send_response_after
     *
     * @param $observer
     * @return void
     */
    public function cachePage(Varien_Event_Observer $observer) {
        /**
         * @var $engine Brim_PageCache_Model_Engine
         */

        /**
         * prevents method from being called twice. Used for Compat with Magento pre 1.4.2,
         * where controller_front_send_response_before is optimal but does not exist.
         */
        if ($this->_cachePageCalled == true) {
            return $this;
        }
        $this->_cachePageCalled = true;

        $engine = Mage::getSingleton('brim_pagecache/engine');

        if (!$engine->isEnabled()) {
            return;
        }

        $engine->cachePage($observer->getFront()->getResponse());

        Mage::getSingleton('brim_pagecache/processor_level1')->setNoCacheFlag();
        $engine->updateSessionVarsCookie();
    }

    /**
     * Checks cache for cached pages.  If found the original response is served,
     * otherwise normal processing will occur.
     *
     * Observes: controller_action_predispatch
     *
     * @param $observer
     * @return void
     */
    public function checkAndServeCachedPage(Varien_Event_Observer $observer) {
        /**
         * @var $controllerAction Mage_Core_Controller_Varien_Action
         * @var $engine Brim_PageCache_Model_Engine
         */

        /*
         * controller_action_predispatch occurs before the autoloader sets the scope for the compiler. When the compiler
         * is enabled there is the possibility of creating a redeclare class fatal error if we need any class that is
         * included in the compilers current scope file.  ESP. an issue with the checkout scope.  Registering the scope
         * here prevents the fatal error from occurring.
         */
        if (defined('COMPILER_INCLUDE_PATH')) {
            $routeName = $observer->getControllerAction()->getRequest()->getRouteName();
            if (Varien_Autoload::getScope() != $routeName) {
                Varien_Autoload::registerScope($routeName);
            }
        }

        $engine = Mage::getSingleton('brim_pagecache/engine');

        if (!$engine->isEnabled()) {
            return;
        }

        $engine->servePage($this);
    }

    /**
     * Disabled storing and removes parameters stored in the session
     *
     * @return void
     */
    public function disableParamMemorization() {
        if (!$this->getEngine()->isEnabled()) {
            return;
        }
        $catalogSession = Mage::getSingleton('catalog/session');
        $catalogSession->setParamsMemorizeDisabled(true)
            ->unsSortOrder()
            ->unsSortDirection()
            ->unsDisplayMode()
            ->unsLimitPage();
        return;
    }

    /**
     * Handles appending custom FPC updates to the pages layout.
     *
     * Observes: controller_action_layout_generate_xml_before
     *
     * @param Varien_Event_Observer $observer
     * @return mixed
     */
    public function addLayoutUpdates(Varien_Event_Observer $observer) {
        /**
         * @var $layout Mage_Core_Model_Layout
         * @var $update Mage_Core_Model_Layout_Update
         * @var $child  Mage_Core_Model_Layout_Element
         */

        if (!$this->getEngine()->isEnabled()) {
            return;
        }

        $layout = $observer->getLayout();
        $update = $layout->getUpdate();

        if (($blockUpdates = Mage::getStoreConfig(Brim_PageCache_Model_Config::XML_PATH_LAYOUT_BLOCK_UPDATES)) != '') {

            foreach (explode("\n", $blockUpdates) as $blockUpdate) {
                @list($blockName, $containerName) = explode(",", trim($blockUpdate));

                if (empty($containerName)) {
                    $containerName = 'brim_pagecache/container_default';
                }

$layoutXml = <<< EOT
<reference name="$blockName">
    <action method="setDynamicBlockContainer"><container>$containerName</container></action>
</reference>
EOT;

                $update->addUpdate($layoutXml);
            }

        }

        if (($customXml = Mage::getStoreConfig(Brim_PageCache_Model_Config::XML_PATH_LAYOUT_CUSTOM_XML)) != '') {
            $customSimpleXml = simplexml_load_string("<update_xml>" . $customXml . "</update_xml>", $update->getElementClass());

            $validChildNodes = $layout->getUpdate()->getHandles();
            $validChildNodes[] = 'block';
            $validChildNodes[] = 'reference';

            foreach ($customSimpleXml->children() as $child) {
                if (in_array($child->getName(), $validChildNodes)) {
                    if (isset($child->update)) {
                        // Processes update tags, ex: <update handle="brim_pagecache_default" />
                        $update->fetchRecursiveUpdates($child);
                    }
                    $update->addUpdate($child->innerXml());
                }
            }
        }
    }

    /**
     * Wraps blocks with a dynamic marker.  Used to ID blocks in cached pages
     * supporting dynamic updates.
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function markDynamicBlocks(Varien_Event_Observer $observer) {
        /**
         * @var $engine Brim_PageCache_Model_Engine
         */

        $engine = Mage::getSingleton('brim_pagecache/engine');

        if (!$engine->isEnabled()) {
            return;
        }

        if ($engine->isPageCachable()) {
            $block      = $observer->getEvent()->getBlock();
            if ($block->getDynamicBlockContainer() != '') {

                $engine->debug($block->getDynamicBlockContainer());

                // create container model
                $container  = $block->getDynamicBlockContainer();
                $modelClass = Mage::app()->getConfig()->getModelClassName($container);

                $argsArray  = array_merge(
                    array('container' => $container),
                    // using call_user_func for pre PHP 5.3 compat
                    call_user_func("$modelClass::getContainerArgs", $block)
                );

                $transport  = $observer->getEvent()->getTransport();
                if ($transport != null) {
                    // mark the dynamic content, Magento 1.4.1.1+
                    $engine->markContentViaTransport($argsArray, $transport);
                } else {
                    // Magento 1.4.0.1 compat
                    $engine->markContentViaFrameTags($argsArray, $block);
                    $engine->debug('Wrapping content via frame tags, other extensions maybe prevent this from working.');
                }
            }
        }
    }

    /**
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function prepareDynamicBlocks(Varien_Event_Observer $observer) {
        
        $engine = Mage::getSingleton('brim_pagecache/engine');

        if (!$engine->isEnabled()) {
            return;
        }

        if ($engine->isPageCachable()) {
            $block      = $observer->getEvent()->getBlock();
            if (($call = $block->getDynamicBlockCall()) != '') {

                list($class, $method) = explode('::', $call);
                $class = Mage::app()->getConfig()->getModelClassName($class);

                $engine->debug("Calling: $class::$method");
                // using call_user_func for pre PHP 5.3 compat
                call_user_func("$class::$method", $block);
            }
        }
    }

    /**
     * Clears specific product pages from FPC after product save.
     *
     * Observes : catalog_product_save_after
     *
     * @param Varien_Event_Observer $observer
     * @return mixed
     */
    public function cleanProductCache(Varien_Event_Observer $observer) {
        /**
         * @var $engine Brim_PageCache_Model_Engine
         * @var $product  Mage_Catalog_Model_Product
         */

        $engine = Mage::getSingleton('brim_pagecache/engine');

        if (!$engine->isEnabled()) {
            return;
        }

        $engine->devDebug(__METHOD__);

        if (Mage::getStoreConfig(Brim_PageCache_Model_Config::XML_PATH_INVALIDATE) == Brim_PageCache_Model_Config::INVALIDATE_FLAG) {
            $engine->getCacheInstance()->invalidateType('brim_pagecache');
        } else {
            $product    = $observer->getEvent()->getProduct();
            $productTags= $this->_findRelatedProductTags($product->getId());
            $engine->devDebug($productTags);
            $engine->getCacheInstance()->clean($productTags);
        }
    }

    /**
     * Clears specific category pages from FPC after category save.
     *
     * Observes : catalog_category_save_after
     *
     * @param Varien_Event_Observer $observer
     * @return mixed
     */
    public function cleanCategoryCache(Varien_Event_Observer $observer) {
        /**
         * @var $engine Brim_PageCache_Model_Engine
         * @var $category  Mage_Catalog_Model_Category
         */

        $engine = Mage::getSingleton('brim_pagecache/engine');

        if (!$engine->isEnabled()) {
            return;
        }

        $engine->devDebug(__METHOD__);

        if (Mage::getStoreConfig(Brim_PageCache_Model_Config::XML_PATH_INVALIDATE) == Brim_PageCache_Model_Config::INVALIDATE_FLAG) {
            $engine->getCacheInstance()->invalidateType('brim_pagecache');
        } else {
            $category    = $observer->getEvent()->getCategory();
            $categoryTags= Brim_PageCache_Model_Engine::FPC_TAG . '_CATEGORY_' . $category->getId();
            $engine->devDebug($categoryTags);
            $engine->getCacheInstance()->clean($categoryTags);
        }
    }

    /**
     * Clears a specific cms page from FPC after saved.
     *
     * @param Varien_Event_Observer $observer
     * @return mixed
     */
    public function cleanCMSPageCache(Varien_Event_Observer $observer) {
        /**
         * @var $engine Brim_PageCache_Model_Engine
         * @var $category  Mage_Catalog_Model_Category
         */

        $engine = Mage::getSingleton('brim_pagecache/engine');

        if (!$engine->isEnabled()) {
            return;
        }

        $page   = $observer->getEvent()->getDataObject();

        if (Mage::getStoreConfig(Brim_PageCache_Model_Config::XML_PATH_INVALIDATE) == Brim_PageCache_Model_Config::INVALIDATE_FLAG) {
            $engine->getCacheInstance()->invalidateType('brim_pagecache');
        } else {
            $tags   = Brim_PageCache_Model_Engine::FPC_TAG . '_CMS_PAGE_' . $page->getId();
            $engine->devDebug(__METHOD__, $tags);
            $engine->getCacheInstance()->clean($tags);
        }
    }

    /**
     * Clears specific product pages from FPC after a successful order in order in case of stock change.
     *
     * Observes : sales_model_service_quote_submit_success
     *
     * @param Varien_Event_Observer $observer
     *
     */
    public function cleanProductCacheAfterOrder(Varien_Event_Observer $observer) {
        /**
         * @var $engine Brim_PageCache_Model_Engine
         * @var $quote  Mage_Sales_Model_Quote
         */

        $engine = Mage::getSingleton('brim_pagecache/engine');
        if (!$engine->isEnabled()) {
            return;
        }

        $engine->devDebug(__METHOD__);

        if (Mage::getStoreConfig(Brim_PageCache_Model_Config::XML_PATH_INVALIDATE) == Brim_PageCache_Model_Config::INVALIDATE_FLAG) {
            //$engine->getCacheInstance()->invalidateType('brim_pagecache');
        } else {
            $tags   = array();
            $quote  = $observer->getEvent()->getQuote();
            foreach ($quote->getAllItems() as $item) {
                $tags[] = Brim_PageCache_Model_Engine::FPC_TAG . '_PRODUCT_' . $item->getProductId();
                if (($children   = $item->getChildrenItems()) != null) {
                    foreach ($children as $childItem) {
                        $tags[] = Brim_PageCache_Model_Engine::FPC_TAG . '_PRODUCT_' . $childItem->getProductId();
                    }
                }
            }
            $engine->devDebug($tags);
            $engine->getCacheInstance()->clean($tags);
        }
    }

    /**
     * Clears products from the cache when their stock status changes.
     *
     * Observes: cataloginventory_stock_item_save_after
     *
     * @param Varien_Event_Observer $observer
     * @return mixed
     */
    public function cleanProductCacheOnStockChange(Varien_Event_Observer $observer) {
        /**
         * @var $engine Brim_PageCache_Model_Engine
         * @var $stockItem  Mage_CatalogInventory_Model_Stock_Item
         * @var $productRelation Mage_Catalog_Model_Resource_Product_Relation
         * @var $writeConnection Varien_Db_Adapter_Interface
         */
        $engine = Mage::getSingleton('brim_pagecache/engine');
        if (!$engine->isEnabled()) {
            return;
        }

        if (Mage::getStoreConfig(Brim_PageCache_Model_Config::XML_PATH_INVALIDATE) == Brim_PageCache_Model_Config::INVALIDATE_FLAG) {
            //$engine->getCacheInstance()->invalidateType('brim_pagecache');
        } else {
            $stockItem = $observer->getEvent()->getItem();

            $engine->devDebug(__METHOD__);

            // Original data is empty when called from the checkout and is only called when the stock status changes.
            if ($stockItem->getOrigData() == null
                || $stockItem->getData('is_in_stock') != $stockItem->getOrigData('is_in_stock')) {
                // stock status has changed
                $tags = $this->_findRelatedProductTags($stockItem->getProductId());
                $engine->devDebug($tags);

                $engine->getCacheInstance()->clean($tags);
            }
        }
        return;
    }

    /**
     * Works up the product relations table generating tags for the parent chain.
     *
     * @param $productId
     * @return array
     */
    protected function _findRelatedProductTags($productId) {
        $productRelation = Mage::getResourceModel('catalog/product_relation');
        $writeConnection = Mage::getSingleton('core/resource')->getConnection('core_write');

        $childIds   = array($productId);
        $tags       = array(Brim_PageCache_Model_Engine::FPC_TAG . '_PRODUCT_' . $productId);
        do {
            // following product relations up the chain.
            $select = $writeConnection->select()
                ->from($productRelation->getMainTable(), array('parent_id'))
                ->where("child_id IN (?)", $childIds);
                ;
            if (($childIds = $select->query()->fetchAll(Zend_Db::FETCH_COLUMN, 0))) {
                foreach ($childIds as $id) {
                    $tags[] = Brim_PageCache_Model_Engine::FPC_TAG . '_PRODUCT_' . $id;
                }
            }
        } while($childIds != null);

        return $tags;
    }

    /**
     * Clears products in the FPC that match the catalog price rule.
     *
     * Observes : catalogrule_rule_save_after
     *
     * @param Varien_Event_Observer $observer
     * @return mixed
     */
    public function cleanProductsInCatalogPriceRule(Varien_Event_Observer $observer) {
        /**
         * @var $engine Brim_PageCache_Model_Engine
         * @var $rule Mage_CatalogRule_Model_Rule
         */

        $engine = Mage::getSingleton('brim_pagecache/engine');

        if (!$engine->isEnabled()) {
            return;
        }

        $engine->devDebug(__METHOD__);

        if (Mage::getStoreConfig(Brim_PageCache_Model_Config::XML_PATH_INVALIDATE) == Brim_PageCache_Model_Config::INVALIDATE_FLAG) {
            $engine->getCacheInstance()->invalidateType('brim_pagecache');
        } else {
            $tags   = array();
            $rule  = $observer->getEvent()->getRule();
            foreach ($rule->getMatchingProductIds() as $productId) {
                $tags[] = Brim_PageCache_Model_Engine::FPC_TAG . '_PRODUCT_' . $productId;
            }
            $engine->devDebug($tags);
            $engine->getCacheInstance()->clean($tags);
        }
    }

    /**
     * Adds cache tags for products in a product listing.
     *
     * Observes : catalog_block_product_list_collection
     *
     * @param Varien_Event_Observer $observer
     * @return mixed
     */
    public function registerProductTags(Varien_Event_Observer $observer) {
        /**
         * @var $engine Brim_PageCache_Model_Engine
         * @var $collection Mage_Eav_Model_Entity_Collection_Abstract
         */

        $engine = Mage::getSingleton('brim_pagecache/engine');

        if (!$engine->isEnabled()) {
            return;
        }

        $engine->devDebug(__METHOD__);

        $tags       = array();
        $collection = $observer->getEvent()->getCollection();
        foreach ($collection as $product) {
            $tags[] = Brim_PageCache_Model_Engine::FPC_TAG . '_PRODUCT_' . $product->getId();
        }
        $engine->devDebug($tags);

        $engine->registerPageTags($tags);
    }

    /**
     * Checks block for known objects and registers cache tags with the engine based on it's findings.
     *
     * Observes : core_block_abstract_to_html_after
     *
     * @param Varien_Event_Observer $observer
     * @return mixed
     */
    public function registerTags(Varien_Event_Observer $observer) {
        /**
         * @var $engine Brim_PageCache_Model_Engine
         */

        $engine = Mage::getSingleton('brim_pagecache/engine');

        if (!$engine->isEnabled()) {
            return;
        }

        $tags   = array();
        $event  = $observer->getEvent();
        $block  = $event->getBlock();

        // Add CMS Page Tag
        if ($block instanceof Mage_Cms_Block_Page && ($page = $block->getPage()) != null) {
            $tags[] = Brim_PageCache_Model_Engine::FPC_TAG . '_CMS_PAGE_' . $page->getId();
        }

        if (count($tags) > 0) {
            $engine->devDebug(__METHOD__, $tags);
            $engine->registerPageTags($tags);
        }
    }

    /*
     * Section
     */

    /**
     * Generates affected block cache keys.
     *
     * Observers: sales_quote_save_after
     *
     * @param Varien_Event_Observer $observer
     * @return mixed
     */
    public function updateBlockKeysOnQuoteSave(Varien_Event_Observer $observer) {
        $engine = Mage::getSingleton('brim_pagecache/engine');

        if (!$engine->isEnabled()) {
            return;
        }

        Mage::getSingleton('brim_pagecache/container_cart')->generateCacheKey();
        Mage::getSingleton('brim_pagecache/container_accountlinks')->generateCacheKey();

    }

    /**
     * Generates affected block cache keys.
     *
     * Observers: customer_login
     *
     * @param Varien_Event_Observer $observer
     * @return mixed
     */
    public function updateBlockKeysOnCustomerLogin(Varien_Event_Observer $observer) {
        $engine = Mage::getSingleton('brim_pagecache/engine');

        if (!$engine->isEnabled()) {
            return;
        }

        Mage::getSingleton('brim_pagecache/container_cart')->generateCacheKey();
        Mage::getSingleton('brim_pagecache/container_accountlinks')->generateCacheKey();
    }

    /**
     * Generates affected block cache keys.
     *
     * Observers: customer_logout
     *
     * @param Varien_Event_Observer $observer
     * @return mixed
     */
    public function updateBlockKeysOnCustomerLogout(Varien_Event_Observer $observer) {
        $engine = Mage::getSingleton('brim_pagecache/engine');

        if (!$engine->isEnabled()) {
            return;
        }

        Mage::getSingleton('brim_pagecache/container_cart')->generateCacheKey();
        Mage::getSingleton('brim_pagecache/container_accountlinks')->generateCacheKey();
    }

    /**
     * Generates affected block cache keys.
     *
     * Observers: wishlist_wishlist_save_after
     *
     * @param Varien_Event_Observer $observer
     * @return mixed
     */
    public function updateBlockKeysOnWishlistSave(Varien_Event_Observer $observer) {
        $engine = Mage::getSingleton('brim_pagecache/engine');

        if (!$engine->isEnabled()) {
            return;
        }

        Mage::getSingleton('brim_pagecache/container_accountlinks')->generateCacheKey();
    }

    /**
     * Generates affected block cache keys.
     *
     * Observers:
     *  catalog_product_compare_add_product
     *  catalog_product_compare_remove_product
     *  catalog_product_compare_item_collection_clear
     *
     * @param Varien_Event_Observer $observer
     * @return mixed
     */
    public function updateBlockKeysOnCompareChange(Varien_Event_Observer $observer) {
        $engine = Mage::getSingleton('brim_pagecache/engine');

        if (!$engine->isEnabled()) {
            return;
        }

        Mage::getSingleton('brim_pagecache/container_compared')->generateCacheKey();
    }

    /**
     * Preloads the orignal request to prevent other extension from changing it.
     *
     * Observes: controller_front_init_routers
     *
     * @return void
     */
    public function saveOriginalRequest() {
        $request = Mage::app()->getRequest()->getOriginalRequest();
        Mage::register('brim_pagecache_original_request', $request);
    }
}