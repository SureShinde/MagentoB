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


class Brim_PageCache_Model_Config {

    const XML_PATH_ENABLED                  = 'brim_pagecache/settings/enabled';
    const XML_PATH_ENABLE_LEVEL1            = 'brim_pagecache/settings/enable_level1';
    const XML_PATH_ENABLE_BLOCK_UPDATES     = 'brim_pagecache/settings/enable_block_updates';
    const XML_PATH_CACHE_BLOCK_UPDATES      = 'brim_pagecache/settings/cache_block_updates';
    const XML_PATH_ENABLE_MINIFY_HTML       = 'brim_pagecache/settings/enable_minify_html';
    const XML_PATH_EXPIRES                  = 'brim_pagecache/settings/expires';
    const XML_PATH_INVALIDATE               = 'brim_pagecache/settings/invalidate_clean';
    const XML_PATH_DEBUG                    = 'brim_pagecache/settings/debug';
    const XML_PATH_DEBUG_RESPONSE           = 'brim_pagecache/settings/debug_response';

    const XML_PATH_CONDITIONS_USE_CTR_GRP   = 'brim_pagecache/conditions/use_customer_group';
    const XML_PATH_CONDITIONS_CMB_CTR_GRP   = 'brim_pagecache/conditions/combine_visitor_general_groups';
    const XML_PATH_CONDITIONS_MAX_PARAMS    = 'brim_pagecache/conditions/max_params';
    const XML_PATH_CONDITIONS_SESSION_VARS  = 'brim_pagecache/conditions/session_vars';
    const XML_PATH_CONDITIONS_IGNORE_PARAMS = 'brim_pagecache/conditions/ignore_params';

    const XML_PATH_MOBILE_ENABLE            = 'brim_pagecache/mobile/enable';
    const XML_PATH_MOBILE_USER_AGENT        = 'brim_pagecache/mobile/user_agent';

    const XML_PATH_LAYOUT_BLOCK_UPDATES     = 'brim_pagecache/layout/custom_block_updates';
    const XML_PATH_LAYOUT_ADD_HANDLES       = 'brim_pagecache/layout/additional_handles';
    const XML_PATH_LAYOUT_CUSTOM_XML        = 'brim_pagecache/layout/custom_xml';

    const XML_PATH_STORAGE_USE_SYSTEM_CACHE = 'brim_pagecache/storage/system_cache';
    const XML_PATH_STORAGE_TYPE             = 'brim_pagecache/storage/type';
    const XML_PATH_STORAGE_OVERRIDE_FILE    = 'brim_pagecache/storage/override_system_file';
    const XML_PATH_STORAGE_SLOW_BACKEND     = 'brim_pagecache/storage/slow_backend';
    const XML_PATH_STORAGE_FILE_PATH        = 'brim_pagecache/storage/file_path';
    const XML_PATH_STORAGE_MEMCACHED_SERVERS= 'brim_pagecache/storage/memcached_servers';
    const XML_PATH_STORAGE_REDIS_CONFIG_XML = 'brim_pagecache/storage/redis_config_xml';

    const XML_PATH_CONFIG_AUTOWRITE         = 'brim_pagecache/config/autowrite';

    const INVALIDATE_FLAG                   = 0;
    const FORCE_CLEAN_FLAG                  = 1;

    const STORAGE_TYPE_MAGENTO              = 'default';
    const STORAGE_TYPE_FILE                 = 'file';
    const STORAGE_TYPE_FILE_SCALE           = 'Brim_PageCache_Model_Backend_File_Scalable';
    const STORAGE_TYPE_APC                  = 'apc';
    const STORAGE_TYPE_MEMCACHED            = 'memcached';
    const STORAGE_TYPE_XCACHE               = 'xcache';
    const STORAGE_TYPE_DATABASE             = 'database';
    const STORAGE_TYPE_REDIS                = 'redis';
    const STORAGE_TYPE_OPT_FILE             = 'Mage_Cache_Backend_File';

    const SESSION_VARS_COOKIE_NAME          = 'FPC_SVARS';

    static public function factoryCache($options=array()) {
        $config = array();

        $useSystemCache = Mage::getStoreConfig(Brim_PageCache_Model_Config::XML_PATH_STORAGE_USE_SYSTEM_CACHE);

        if (!$useSystemCache) {
            $backend        = Mage::getStoreConfig(Brim_PageCache_Model_Config::XML_PATH_STORAGE_TYPE);

            if ($backend == self::STORAGE_TYPE_FILE) {
                $backend = '';
            }

            $config['backend'] = $backend;

            // File Backend options
            if ($backend == '' || $backend == self::STORAGE_TYPE_FILE_SCALE || $backend == self::STORAGE_TYPE_OPT_FILE) {
                if (($cacheDir = Mage::getStoreConfig(Brim_PageCache_Model_Config::XML_PATH_STORAGE_FILE_PATH)) != '') {
                    if (!isset($options['relative_dir']) || $options['relative_dir'] == false) {
                        $cacheDir = Mage::getBaseDir('var') . DS . $cacheDir;
                    }

                    $config['backend_options']['cache_dir'] = $cacheDir;
                }
            }

            $resource                   = Mage::getSingleton('core/resource');

            // Scalable file only settings
            if ($backend == self::STORAGE_TYPE_FILE_SCALE) {
                $config['backend_options']['database'] = array(
                    'data_table'    => $resource->getTableName('brim_pagecache/cache'),
                    'tags_table'    => $resource->getTableName('brim_pagecache/cache_tag')
                );
            // APC/Memcached config
            } else if (in_array($backend, array(self::STORAGE_TYPE_APC, self::STORAGE_TYPE_MEMCACHED))) {
                $slowBackend = Mage::getStoreConfig(Brim_PageCache_Model_Config::XML_PATH_STORAGE_SLOW_BACKEND);

                // File is the default don't set as an exception is thrown
                if ($slowBackend != self::STORAGE_TYPE_FILE) {
                    $config['slow_backend'] = $slowBackend;

                    $config['slow_backend_options'] = array(
                        'data_table'    => $resource->getTableName('brim_pagecache/cache'),
                        'tags_table'    => $resource->getTableName('brim_pagecache/cache_tag')
                    );
                } else {
                    if (($slowCacheDir = Mage::getStoreConfig(Brim_PageCache_Model_Config::XML_PATH_STORAGE_FILE_PATH)) != '') {
                        if (!isset($options['relative_dir']) || $options['relative_dir'] == true) {
                            $slowCacheDir = Mage::getBaseDir('var') . DS . $slowCacheDir;
                        }

                        // Second level does not inherit Mage_Core_Model_Cache defaults if any options are given
                        $config['slow_backend_options'] = array(
                            'cache_dir'                 => $slowCacheDir,
                            'hashed_directory_level'    => 1,
                            'hashed_directory_umask'    => '0777',
                            'file_name_prefix'          => 'fpc',
                        );
                    }
                }
                if ($backend == self::STORAGE_TYPE_MEMCACHED) {
                    $servers = Mage::getStoreConfig(Brim_PageCache_Model_Config::XML_PATH_STORAGE_MEMCACHED_SERVERS);

                    $memcachedServers = simplexml_load_string($servers, 'Mage_Core_Model_Config_Element');

                    $config[self::STORAGE_TYPE_MEMCACHED]['servers'] = $memcachedServers->asArray();
                }

            } else if ($backend == self::STORAGE_TYPE_OPT_FILE) {

                if (!class_exists($fileClass = 'Mage_Cache_Backend_File')) {
                    $fileClass = 'Cm_Cache_Backend_File';
                }

                $config['backend'] = $fileClass;

            // REDIS
            } else if ($backend == self::STORAGE_TYPE_REDIS) {

                if (!class_exists($redisClass = 'Mage_Cache_Backend_Redis')) {
                    $redisClass = 'Cm_Cache_Backend_Redis';
                }

                $config['backend'] = $redisClass;
                $servers        = Mage::getStoreConfig(Brim_PageCache_Model_Config::XML_PATH_STORAGE_REDIS_CONFIG_XML);
                $redisServers   = simplexml_load_string($servers, 'Mage_Core_Model_Config_Element');

                $config['backend_options'] = $redisServers->asArray();
            }
        }

        return $config;
    }
}