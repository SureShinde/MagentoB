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

class Brim_PageCache_Model_Adminhtml_Observer extends Varien_Event_Observer {
    /**
      * Observes:
      * controller_action_postdispatch_adminhtml_system_config_save,
      * controller_action_postdispatch_adminhtml_catalog_product_save,
      * controller_action_postdispatch_adminhtml_catalog_product_action_attribute_save,
      * controller_action_postdispatch_adminhtml_catalog_product_massStatus,
      * catalogrule_after_apply

      * @param $observer
      * @return
      */
    public function invalidateCache($observer) {
        /**
         * @var $engine Brim_PageCache_Model_Engine
         */
        $engine = Mage::getSingleton('brim_pagecache/engine');

        if (!$engine->isExtensionEnabled()) {
            return;
        }

        $engine->debug(__METHOD__);
        $engine->debug($observer->getEvent()->getName());

        try {
            if (Mage::getStoreConfig(Brim_PageCache_Model_Config::XML_PATH_INVALIDATE) == Brim_PageCache_Model_Config::INVALIDATE_FLAG) {
                $engine->getCacheInstance()->invalidateType('brim_pagecache');
            } else {
                if (Mage::getStoreConfig(Brim_PageCache_Model_Config::XML_PATH_STORAGE_USE_SYSTEM_CACHE) == 1) {
                    $engine->getCacheInstance()->cleanType('brim_pagecache');
                } else {
                    $engine->getCacheInstance()->flush();
                }
            }
        } catch (Exception $e) {
            $engine->debug($e);
            Mage::logException($e);
        }
    }

    /**
     * Clean cache of any and all cached pages.
     *
     * Observes:
     * application_clean_cache
     *
     * @param $observer
     * @return void
     */
    public function cleanPageCache($observer) {
        /**
         * @var $engine Brim_PageCache_Model_Engine
         */
        $engine = Mage::getSingleton('brim_pagecache/engine');

        if (!$engine->isExtensionEnabled()) {
            return;
        }

        $engine->debug(__METHOD__);
        $engine->debug($observer->getEvent()->getName());

        try {
            if (Mage::getStoreConfig(Brim_PageCache_Model_Config::XML_PATH_STORAGE_USE_SYSTEM_CACHE) == 1) {
                $engine->getCacheInstance()->cleanType('brim_pagecache');
            } else {
                $engine->getCacheInstance()->flush();
            }
        } catch (Exception $e) {
            $engine->debug($e);
            Mage::logException($e);
        }
    }

    /**
     * @param $observer
     */
    public function handleTypeRefresh($observer) {
        /**
         * @var $engine Brim_PageCache_Model_Engine
         */
        $engine = Mage::getSingleton('brim_pagecache/engine');

        if (!$engine->isExtensionEnabled()) {
            return;
        }

        $engine->debug(__METHOD__);
        $engine->debug($observer->getEvent()->getName());

        if ($observer->getType() == 'brim_pagecache') {
            try {
                if (Mage::getStoreConfig(Brim_PageCache_Model_Config::XML_PATH_STORAGE_USE_SYSTEM_CACHE) == 1) {
                    $engine->getCacheInstance()->cleanType('brim_pagecache');
                } else {
                    $engine->getCacheInstance()->flush();
                }
            } catch (Exception $e) {
                $engine->debug($e);
                Mage::logException($e);
            }
        }
    }

    /**
     * @param $observer
     */
    public function configChanged($observer) {
        /**
         * @var $sxml Mage_Core_Model_Config_Element
         * @var $fpcConfig Mage_Core_Model_Config_Element
         */

        $etcDir     = Mage::getConfig()->getOptions()->getEtcDir();
        $localConfig= $etcDir . DS . 'brim_pagecache.xml';

        // Ensure the brim_pagecache.xml can be created or updated based on directory and file permissions.
        if ((!file_exists($localConfig) && !is_writable($etcDir)) || (file_exists($localConfig) && !is_writable($localConfig))) {
            Mage::throwException("Unable to create and/or modify $localConfig");
        }

        if (!file_exists($localConfig)) {
            $sxml = simplexml_load_string('<config><global><cache></cache></global></config>', 'Brim_PageCache_Model_Config_Element');
        } else {
            $sxml = simplexml_load_file($localConfig, 'Brim_PageCache_Model_Config_Element');
        }

        $global      = $sxml->global;

        if (!isset($global->cache)) {
            $global->addChild('cache');
        }

        $globalCache = $sxml->global->cache;

        // Clean old configuration if it exists
        if (isset($globalCache->request_processors)) {
            if (isset($globalCache->request_processors->children()->brim_pagecache)) {
                unset ($globalCache->request_processors->children()->brim_pagecache);

                if (count($globalCache->request_processors->children()) == 0) {
                    unset ($globalCache->request_processors);
                }
            }
        }

        if (isset($globalCache->children()->backend)) {
            unset ($globalCache->children()->backend);
        }

        if (isset($globalCache->children()->brim_pagecache)) {
            unset ($globalCache->children()->brim_pagecache);
        }

        if (isset($global->children()->brim_pagecache)) {
            unset ($global->children()->brim_pagecache);
        }

        // Add new configuration to simple xml
        $fpcConfigFileNeeded = false;

        if (Mage::getStoreConfig(Brim_PageCache_Model_Config::XML_PATH_ENABLE_LEVEL1) == '1') {
            if(!$globalCache->request_processors) {
               $globalCache->addChild('request_processors');
            }
            $globalCache->request_processors->addChild('brim_pagecache', 'Brim_PageCache_Model_Processor_Level1');


            $ignoreParams = Mage::getStoreConfig(Brim_PageCache_Model_Config::XML_PATH_CONDITIONS_IGNORE_PARAMS);
            $sessionVariables = Mage::getStoreConfig(Brim_PageCache_Model_Config::XML_PATH_CONDITIONS_SESSION_VARS);

            $pageCacheConfig    = $global->addChild('brim_pagecache');

            if ($ignoreParams != false || $sessionVariables != false) {
                $pageCacheConditions= $pageCacheConfig->addChild('conditions');

                if ($ignoreParams != false) {
                    $pageCacheConditions->addChild('ignore_params', $ignoreParams);
                }

                if ($sessionVariables != false) {
                    $pageCacheConditions->addChild('session_vars', $sessionVariables);
                }
            }

            if (($mobileEnable = Mage::getStoreConfig(Brim_PageCache_Model_Config::XML_PATH_MOBILE_ENABLE)) != false) {
                if (($mobileUserAgent = Mage::getStoreConfig(Brim_PageCache_Model_Config::XML_PATH_MOBILE_USER_AGENT)) != false) {
                    $mobileConfig       = $pageCacheConfig->addChild('mobile');
                    $mobileConfig->addChild('user_agent', $mobileUserAgent);
                }
            }

            // Generate config options
            $cacheConfig    = Brim_PageCache_Model_Config::factoryCache(array('relative_dir' => true));
            if (count($cacheConfig) > 0) {
                $fpcConfig      = $globalCache->addChild('brim_pagecache');
                Mage::helper('brim_pagecache')->addOptionsToXml($fpcConfig, $cacheConfig);
            }

            $fpcConfigFileNeeded = true;
        }

        if (Mage::getStoreConfig(Brim_PageCache_Model_Config::XML_PATH_STORAGE_USE_SYSTEM_CACHE)
            &&  Mage::getStoreConfig(Brim_PageCache_Model_Config::XML_PATH_STORAGE_OVERRIDE_FILE)) {
            $globalCache->addChild('backend', Brim_PageCache_Model_Config::STORAGE_TYPE_FILE_SCALE);
            $fpcConfigFileNeeded = true;
        }

        // persist cache options if required
        if ($fpcConfigFileNeeded == false) {
            if (file_exists($localConfig)) {
                if (is_link($localConfig)) {
                    if (isset($global->cache)) {
                        unset($global->cache);
                    }
                    $niceXML = $sxml->asNiceXml($localConfig);
                    Mage::app()->getConfig()->saveConfig('brim_pagecache/config/xml', $niceXML);
                } else {
                    if (!unlink($localConfig)) {
                        Mage::throwException('Failed to delete config file: ' . $localConfig);
                    }
                }
            }

            Mage::app()->getConfig()->deleteConfig('brim_pagecache/config/xml');
        } else {
            $niceXML = $sxml->asNiceXml($localConfig);

            Mage::app()->getConfig()->saveConfig('brim_pagecache/config/xml', $niceXML);
        }

        Mage::app()->cleanCache(array('CONFIG'));
    }
}