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


class Brim_PageCache_Model_Cron
{
    /**
     * Cleans old objects from the cache.
     *
     * @param Mage_Cron_Model_Schedule $schedule
     */
    public function cleanOldCache(Mage_Cron_Model_Schedule $schedule) {
        /**
         * @var $engine Brim_PageCache_Model_Engine
         */
        $engine = Mage::getSingleton('brim_pagecache/engine');

        if (!$engine->isExtensionEnabled()) {
            return;
        }

        $engine->getCache()->clean(Zend_Cache::CLEANING_MODE_OLD);
    }

    /**
     * Clean up the database cache tags.
     *
     * @param Mage_Cron_Model_Schedule $schedule
     * @return $this
     */
    public function cleanOrphanedTags(Mage_Cron_Model_Schedule $schedule) {

        try {

            if (Mage::getStoreConfig(Brim_PageCache_Model_Config::XML_PATH_STORAGE_TYPE) == Brim_PageCache_Model_Config::STORAGE_TYPE_DATABASE
                || Mage::getStoreConfig(Brim_PageCache_Model_Config::XML_PATH_STORAGE_SLOW_BACKEND) == Brim_PageCache_Model_Config::STORAGE_TYPE_DATABASE) {

                /** @var Mage_Core_Model_Resource $resource */
                $resource     = Mage::getSingleton('core/resource');

                $dataTable    = $resource->getTableName('brim_pagecache/cache');
                $tagsTable    = $resource->getTableName('brim_pagecache/cache_tag');

                $resource->getConnection('core_write')->delete($tagsTable, "cache_id NOT IN (SELECT id FROM {$dataTable})");

            }

        } catch (Exception $e) {
            Mage::logException($e);
        }

        return $this;
    }
}