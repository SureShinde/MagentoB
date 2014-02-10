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
 * @copyright  Copyright (c) 2011-2013 Brim LLC
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
}