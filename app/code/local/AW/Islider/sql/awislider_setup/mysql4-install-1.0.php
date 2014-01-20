<?php
/**
* aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Islider
 * @version    1.3.1
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 */

$installer = $this;
$installer->startSetup();

$installer->run("
CREATE TABLE IF NOT EXISTS `{$this->getTable('awislider/sliders')}` (
    `id` int(10) unsigned NOT NULL auto_increment,
    `name` tinytext NOT NULL,
    `block_id` tinytext NOT NULL,
    `is_active` tinyint(4) NOT NULL default '1',
    `store` text NOT NULL,
    `autoposition` int(11) NOT NULL,
    `nav_autohide` tinyint(4) NOT NULL default '1',
    `switch_effect` tinytext NOT NULL,
    `width` int(11) NOT NULL,
    `height` int(11) NOT NULL,
    `animation_speed` int(11) NOT NULL default '0',
    `first_timeout` int(11) NOT NULL default '0',
    PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT = 'Slider blocks storage';

CREATE TABLE IF NOT EXISTS `{$this->getTable('awislider/images')}` (
    `id` int(10) unsigned NOT NULL auto_increment,
    `pid` int(10) unsigned NOT NULL,
    `is_active` tinyint(4) NOT NULL default '1',
    `type` tinyint(4) NOT NULL,
    `location` text NOT NULL,
    `title` tinytext NOT NULL,
    `url` text NOT NULL,
    `active_from` date NOT NULL,
    `active_to` date NOT NULL,
    `new_window` tinyint(4) NOT NULL,
    `nofollow` tinyint(4) NOT NULL,
    `clicks_total` int(10) unsigned NOT NULL default '0',
    `clicks_unique` int(10) unsigned NOT NULL default '0',
    `sort_order` int(11) NOT NULL,
    PRIMARY KEY  (`id`) ,
    CONSTRAINT `FK_SLIDER_ID` FOREIGN KEY (`pid`) REFERENCES `{$this->getTable('awislider/sliders')}` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT = 'Slider images storage';
");

/**
 * Creating folder for uploads storage
 */

$path = Mage::getBaseDir('media').DS.Mage::helper('awislider/files')->getFolderName();
if(!file_exists($path))
    @mkdir($path);

$installer->endSetup();
