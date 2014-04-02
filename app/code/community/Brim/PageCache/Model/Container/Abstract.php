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
 * @copyright  Copyright (c) 2011-2014 Brim LLC
 * @license    http://ecommerce.brimllc.com/license
 */


class Brim_PageCache_Model_Container_Abstract {

    protected $_blockArgs = null;

    /**
     * @var Mage_Core_Block_Abstract
     */
    protected $_block     = null;

    /**
     * @var string
     */
    protected $_baseCacheId     = null;

    protected $_cacheKey        = null;

    protected $_sessionCacheKeyName = false;

    /**
     * @var simpleXML
     */
    static protected $_updateCache = null;

    /**
     * @param $args
     */
    public function __construct($args=null) {
        $this->_blockArgs       = $args;
        Varien_Profiler::start('Brim_PageCache::' . get_class($this) . '::_construct');
        $this->_construct($args);
        Varien_Profiler::stop('Brim_PageCache::' . get_class($this) . '::_construct');
    }

    /**
     * Safe constructor for children to override.
     *
     * @param $args
     * @return void
     */
    protected function _construct($args) {
    }

    /**
     * Generates a usable cache id.
     *
     * @return string
     */
    protected function _getCacheId() {
        return 'BRIM_FPC_DYNAMIC_BLOCK_'
            . Mage::app()->getStore()->getCode() . '_'
            . Mage::getDesign()->getPackageName() . '_'
            . Mage::getDesign()->getTheme('layout') . '_'
            . Mage::app()->getLocale()->getLocaleCode() . '_'
            . Mage::app()->getStore()->getCurrentCurrencyCode() . '_'
            // Separate out the cache by customer group.
            // Helps with Logged in and out users for things like account links
            . Mage::getSingleton('customer/session')->getCustomerGroupId() . '_'
            . (($isMobile  = Brim_PageCache_Helper_Mobile::isMobile()) !== null ? $isMobile . '_' : '')
            . (isset($this->_blockArgs['name']) ? $this->_blockArgs['name'] : '')
            . (($key = $this->_getCacheKey()) != null ? '_' . $key : '');
    }

    /**
     * Generates a cache key for a given user / block.  Optionally implemented by sub classed containers.
     * 
     * @return null|string
     */
    protected function _generateCacheKey() {
        return null;
    }

    /**
     * Generates a cache key for a given user / block.
     * 
     * @return null|string
     */
    final public function generateCacheKey() {
        if (!Mage::getStoreConfig(Brim_PageCache_Model_Config::XML_PATH_CACHE_BLOCK_UPDATES)) {
            return null;
        }

        $this->_cacheKey = $this->_generateCacheKey();
        if ($this->_sessionCacheKeyName !== false) {
            Mage::getSingleton('brim_pagecache/session')->setData($this->_sessionCacheKeyName, $this->_cacheKey);
        }
        return $this->_cacheKey;
    }

    /**
     * The cache key is determined by the specific container block.
     *
     * @return string
     */
    protected function _getCacheKey() {
        Varien_Profiler::start('Brim_PageCache::' . get_class($this) . '::_getCacheKey');

        if ($this->_cacheKey == null) {
            if ($this->_sessionCacheKeyName === false ||
                ($this->_cacheKey = Mage::getSingleton('brim_pagecache/session')->getData($this->_sessionCacheKeyName)) == null) {
                $this->generateCacheKey();
            }
        }

        Varien_Profiler::stop('Brim_PageCache::' . get_class($this) . '::_getCacheKey');

        return $this->_cacheKey;
    }

    /**
     * Renders the dynamic block.  Uses cache when possible.
     *
     * @return false|mixed
     */
    final public function renderBlock() {

        if (Mage::getStoreConfig(Brim_PageCache_Model_Config::XML_PATH_CACHE_BLOCK_UPDATES)) {
            $id     = $this->_getCacheId();
            $cache  = Mage::getSingleton('brim_pagecache/engine')->getCache();

            if ($id == false || !($html = $cache->load($id))) {
                $block  = $this->_createBlock();
                $html   = $this->_renderBlock();
                if ($id != false) {
                    Mage::getSingleton('brim_pagecache/engine')->debug('Saving object with cache id: ' . $id);
                    $cache->save(
                        $html,
                        $id,
                    array('BRIM_FPC', 'BRIM_FPC_DYNAMIC_BLOCK'),
                        Mage::getStoreConfig(Brim_PageCache_Model_Config::XML_PATH_EXPIRES)
                    );
                }
            } else {
                //Mage::getSingleton('brim_pagecache/engine')->debug('Block Hit : ' . $id);
            }
        } else {
            $block  = $this->_createBlock();
            $html   = $this->_renderBlock();
        }

        return $html;
    }

    /**
     * Determines if a block requires an update.
     *
     * @return bool
     */
    public function blockRequiresUpdate() {
        return true;
    }

    /**
     * Creates block encoded in the marker for further processing.
     *
     * @return Mage_Core_Block_Abstract|null
     */
    protected function _createBlock() {

        $args   = $this->_blockArgs;

        if (isset($args['layout'])) {
            $layout = new Mage_Core_Model_Layout($args['layout']);
            $layout->generateBlocks();
            $this->_block = $layout->getBlock($args['name']);
        }

        return $this->_block;
    }

    /**
     * Safe method for children to override w/o affecting the cache.
     *
     * @return string
     */
    protected function _renderBlock() {

        if (!$this->_block) {
            return null;
        }

        if(($html = $this->_block->toHtml()) == '') {
            // saves at least a space to prevent additional generation for the same cache key
            $html = ' ';
        }

        return $html;
    }

    /**
     * returns values to be serialized 
     *
     * @static
     * @param $block
     * @return array
     */
    public static function getContainerArgs($block, $options = array()) {
        $defaultOptions = array(
            'block'     => get_class($block),
            'name'      => $block->getNameInLayout(),
            'template'  => $block->getTemplate(),
            'layout'    => self::_generateBlockLayoutXML($block->getNameInLayout()),
            'remove_l1' => false
        );

        $options = array_merge($defaultOptions, $options);

        return $options;
    }

    protected static function _generateBlockLayoutXML($blockName) {

        $engine = Mage::getSingleton('brim_pagecache/engine');

        // get layout sections references our block
        if (self::$_updateCache == null) {
            self::$_updateCache = Mage::app()->getLayout()->getUpdate()->asSimplexml();
        }
        $sections   = self::$_updateCache->xpath("//block[@name='{$blockName}'] | //reference[@name='{$blockName}']");

        // convert section into it's own layout.
        $layoutXml  = "";
        foreach($sections as $section) {
            $layoutXml .= self::_generateSubBlockLayoutXml($section);
        }

        // Processes layout remove instructions
        $layout = new Mage_Core_Model_Layout();
        $layout->getUpdate()->addUpdate($layoutXml);
        $layout->generateXml();
        $layoutXml = $layout->getXmlString();

        return $layoutXml;
    }

    protected static function _generateSubBlockLayoutXml($section) {
        $layoutXml = $section->asXML();
        foreach ($section->xpath("block") as $block) {
            foreach (self::$_updateCache->xpath("//reference[@name='{$block->getBlockName()}']") as $subSection) {
                $layoutXml .= self::_generateSubBlockLayoutXml($subSection);

            }
        }

        return $layoutXml;
    }
}