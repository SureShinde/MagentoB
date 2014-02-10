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

class Brim_PageCache_Model_Processor_Level1 {

    /**
     * @var string
     */
    protected $_fpcCacheId = null;

    /**
     * @var Mage_Core_Model_Cache
     */
    protected $_cache = null;

    /**
     * @var Brim_PageCache_Helper_Data
     */
    protected $_helper = null;

    /**
     * @var Brim_PageCache_Model_Processor_Level1_Cookie
     */
    protected $_cookie = null;

    /**
     * @return Brim_PageCache_Model_Cache|Mage_Core_Model_Cache|mixed
     */
    public function getCache() {
        if (($cache = Mage::registry('brim_pagecache_storage')) == false) {

            $fpcConfig  = Mage::getConfig()->getNode('global/cache/brim_pagecache');
            $options    = $fpcConfig ? $fpcConfig->asArray() : false;

            if ($options == false) {
                $cache = Mage::app()->getCacheInstance();
            } else {
                // module config is not loaded, can not use Mage::getModel();
                $cache  = new Brim_PageCache_Model_Cache($options);
            }

            Mage::register('brim_pagecache_storage', $cache);
        }

        return $cache;
    }

    /**
     * Returns the page cache helper. Config data has not been loaded so created manually.
     *
     * @return Brim_PageCache_Helper_Data|null
     */
    public function getHelper() {
        if ($this->_helper === null) {
            $this->_helper = new Brim_PageCache_Helper_Data();
        }

        return $this->_helper;
    }

    /**
     * @return Brim_PageCache_Model_Processor_Level1_Cookie|null
     */
    public function getCookie() {
        if ($this->_cookie === null) {
            $this->_cookie = new Brim_PageCache_Model_Processor_Level1_Cookie();
        }
        return $this->_cookie;
    }

    /**
     * Saves page content to the cache after removing blocks like recently viewed products.
     *
     * @param $storageObject Brim_PageCache_Model_Storage
     * @param array $tags
     * @param null $expires
     */
    public function save($storageObject, $tags=array(), $expires=null) {
        /**
         * @param $cookie Mage_Core_Model_Cookie
         * @param $l1StorageObject Brim_PageCache_Model_Storage
         *
         */

        $content = $storageObject->getResponse()->getBody();

        // removes blocks like recently viewed products
        foreach ($storageObject->getBlockUpdateData() as $key => $options) {
            if (isset($options['remove_l1']) && $options['remove_l1'] == true) {
                $blockName = preg_quote($options['name']);
                $content = preg_replace(
                    '/\<!\-\- BRIM_FPC ' . $blockName . ' ' . $key . ' \-\-\>(.*)\<!\-\- \/BRIM_FPC ' . $blockName . ' \-\-\>/si',
                    '',
                    $content
                );
            }
        }

        // Store the page content and cookie params in the cache
        $l1StorageObject= Mage::getModel('brim_pagecache/storage');
        $l1StorageObject->setContent($content);

        $l1StorageObject->setResponseHeaders($storageObject->getResponse()->getHeaders());

        $cookie         = Mage::getSingleton('core/cookie');
        $l1StorageObject->setCookieParams(array(
            'domain'    => $cookie->getDomain(),
            'path'      => $cookie->getPath(),
            'lifetime'  => $cookie->getLifetime(),
            'httponly'  => $cookie->getHttponly()
        ));

        $this->getCache()->save(
            serialize($l1StorageObject),
            $this->generateFPCId(),
            $tags,
            $expires
        );
    }

    /**
     * Retrieves page content from cache and prepares response.
     *
     * @param $content
     * @return string
     */
    public function extractContent($content) {
        if (Mage::app()->getCookie()->get('nocache') != 1) {
            try {
                $cacheData = $this->getCache()->load($this->generateFPCId(Mage::app()->getRequest()));

                if ($cacheData) {
                    $storageObject  = unserialize($cacheData);
                    $content        = $storageObject->getContent();

                    // Apply cached response status header to current response
                    $cachedHeaders  = $storageObject->getResponseHeaders();
                    $response       = Mage::app()->getResponse();

                    $this->getHelper()->copyCachedHeadersToResponse(
                        $cachedHeaders,
                        $response
                    );

                    $response->setHeader('Cache-Control', 'no-cache, must-revalidate');
                    $response->setHeader('Pragma', 'no-cache');
                    $response->setHeader('Expires', 'Sat, 26 Jul 1997 05:00:00 GMT');

                    // Renew frontend cookie.
                    $this->getCookie()->setParams($storageObject->getCookieParams())->renew('frontend');
                }
            } catch (Exception $e) {
                echo ($e->__toString());
                exit;
            }
        }
        return $content;
    }

    /**
     * Generates an L1 cache id.  Primarily uses the URI and store code if one is set.
     *
     * @param null $request
     * @return null|string
     */
    public function generateFPCId($request=null) {
        if ($request == null) {
            $request= Mage::app()->getRequest()->getOriginalRequest();
        }

        if ($this->_fpcCacheId == null) {
            $params = Mage::registry('application_params');

            $this->_fpcCacheId =
                'BRIM_FPC_L1_'
                . (($isMobile  = Brim_PageCache_Helper_Mobile::isMobile()) !== null ? ($isMobile . '_') : '')
                . (
                    Brim_PageCache_Helper_Data::normalizeUri(
                        $request->getRequestUri(),
                        (string)Mage::getConfig()->getNode('global/' . Brim_PageCache_Model_Config::XML_PATH_CONDITIONS_IGNORE_PARAMS),
                        $this->getCookie()->get(Brim_PageCache_Model_Config::SESSION_VARS_COOKIE_NAME)
                    ) . '_'
                    . $request->getHttpHost() . '_'
                    . $request->getScheme()
                )
            ;

            if (!empty($params['scope_code'])) {
                $this->_fpcCacheId .= '_' . $params['scope_code'];
            }

            if (!empty($params['scope_type'])) {
                $this->_fpcCacheId .= '_' . $params['scope_type'];
            }
        }

        return $this->_fpcCacheId;
    }

    /**
     *  L1 cache uses a cookie to determine if it can be used.
     */
    public function setNoCacheFlag() {
        if($this->canUseStaticCache()) {
            Mage::app()->getCookie()->delete('nocache');
        } else {
            Mage::app()->getCookie()->set('nocache', 1);
        }
    }

    /**
     * Checks the request and session variables to see if the page could be stored in the L1 cache.
     *
     * @return bool
     */
    public function canUseStaticCache() {

        if (!Mage::getStoreConfig(Brim_PageCache_Model_Config::XML_PATH_ENABLE_LEVEL1)) {
            // prevent caching if disabled
            return false;
        }

        if (isset($_GET['no_cache'])) {
            return false;
        }

        if (!empty($_POST)
            || Mage::getSingleton('customer/session')->isLoggedIn()
            || Mage::getSingleton('checkout/session')->getQuote()->hasItems()
            || Mage::helper('catalog/product_compare')->hasItems()
            || (Mage::getSingleton('core/session')->getMessages()->count()
                + Mage::getSingleton('checkout/session')->getMessages()->count()
                + Mage::getSingleton('customer/session')->getMessages()->count()
                + Mage::getSingleton('catalog/session')->getMessages()->count()) > 0)
        {
            return false;
        }

        return true;
    }
}