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

$installer = $this;
$installer->startSetup();

$createCacheTables = <<<TABLE
    CREATE TABLE IF NOT EXISTS `brim_pagecache_cache` (
            `id` VARCHAR(255) NOT NULL,
            `data` mediumblob,
            `create_time` int(11),
            `update_time` int(11),
            `expire_time` int(11),
            PRIMARY KEY  (`id`),
            KEY `IDX_EXPIRE_TIME` (`expire_time`)
    )ENGINE=InnoDB DEFAULT CHARSET=utf8;

    CREATE TABLE IF NOT EXISTS `brim_pagecache_cache_tag` (
        `tag` VARCHAR(255) NOT NULL,
        `cache_id` VARCHAR(255) NOT NULL,
        KEY `IDX_TAG` (`tag`),
        KEY `IDX_CACHE_ID` (`cache_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
TABLE;

$installer->run($createCacheTables);

$installer->endSetup();