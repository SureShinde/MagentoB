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
 * This package designed for Magento community edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento community edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Onsale
 * @version    2.4.0
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


$installer = $this;

$installer->startSetup();

try {
    $installer->run("

DROP TABLE IF EXISTS {$this->getTable('onsale/rule')};

CREATE TABLE IF NOT EXISTS `{$this->getTable('onsale/rule')}` (
  `rule_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Rule Id',
  `name` varchar(255) DEFAULT NULL COMMENT 'Name',
  `description` text COMMENT 'Description',
  `from_date` datetime DEFAULT NULL COMMENT 'From Date',
  `to_date` datetime DEFAULT NULL COMMENT 'To Date',
  `is_active` smallint(6) NOT NULL DEFAULT '0' COMMENT 'Is Active',
  `conditions_serialized` mediumtext COMMENT 'Conditions Serialized',
  `actions_serialized` mediumtext COMMENT 'Actions Serialized',
  `sort_order` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Sort Order',

  `category_page_show` smallint(5) unsigned NOT NULL DEFAULT '0',
  `category_page_position` varchar(2) DEFAULT NULL,
  `category_page_image` varchar(255) DEFAULT NULL,
  `category_page_img_path` varchar(255) DEFAULT NULL,
  `category_page_text` varchar(255) DEFAULT NULL,
  
  `product_page_show` smallint(5) unsigned NOT NULL DEFAULT '0',
  `product_page_position` varchar(2) DEFAULT NULL,
  `product_page_image` varchar(255) DEFAULT NULL,
  `product_page_img_path` varchar(255) DEFAULT NULL,
  `product_page_text` varchar(255) DEFAULT NULL,

  PRIMARY KEY (`rule_id`),
  KEY `IDX_ONSALE_IS_ACTIVE_SORT_ORDER_TO_DATE_FROM_DATE` (`is_active`,`sort_order`,`to_date`,`from_date`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='OnsaleRule' AUTO_INCREMENT=2 ;

");

    $installer->run("CREATE TABLE IF NOT EXISTS {$this->getTable('onsale/customer_group')} (
  `rule_id` int(10) unsigned NOT NULL COMMENT 'Rule Id',
  `customer_group_id` smallint(5) unsigned NOT NULL COMMENT 'Customer Group Id',
  PRIMARY KEY (`rule_id`,`customer_group_id`),
  KEY `IDX_ONSALE_CUSTOMER_GROUP_RULE_ID` (`rule_id`),
  KEY `IDX_ONSALE_CUSTOMER_GROUP_CUSTOMER_GROUP_ID` (`customer_group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='On Sale Label Rules To Customer Groups Relations';
");

    $installer->run("CREATE TABLE IF NOT EXISTS {$this->getTable('onsale/rule_product')} (
  `rule_product_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Rule Product Id',
  `rule_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Rule Id',
  `from_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'From Time',
  `to_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'To time',
  `customer_group_id` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'Customer Group Id',
  `product_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Product Id',
  `sort_order` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Sort Order',
  `website_id` smallint(5) unsigned NOT NULL COMMENT 'Website Id',
    
  PRIMARY KEY (`rule_product_id`),
  UNIQUE KEY `EAA51B56FF092A0DCB795D1CEF812B7C` (`rule_id`,`from_time`,`to_time`,`website_id`,`customer_group_id`,`product_id`,`sort_order`),
  KEY `IDX_ONSALE_PRODUCT_RULE_ID` (`rule_id`),
  KEY `IDX_ONSALE_PRODUCT_CUSTOMER_GROUP_ID` (`customer_group_id`),
  KEY `IDX_ONSALE_PRODUCT_WEBSITE_ID` (`website_id`),
  KEY `IDX_ONSALE_PRODUCT_FROM_TIME` (`from_time`),
  KEY `IDX_ONSALE_PRODUCT_TO_TIME` (`to_time`),
  KEY `IDX_ONSALE_PRODUCT_PRODUCT_ID` (`product_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='On Sale Rule Product' AUTO_INCREMENT=9 ;");

    $installer->run("CREATE TABLE IF NOT EXISTS {$this->getTable('onsale/website')} (
  `rule_id` int(10) unsigned NOT NULL COMMENT 'Rule Id',
  `website_id` smallint(5) unsigned NOT NULL COMMENT 'Website Id',
  PRIMARY KEY (`rule_id`,`website_id`),
  KEY `IDX_ONSALE_WEBSITE_RULE_ID` (`rule_id`),
  KEY `IDX_ONSALE_WEBSITE_WEBSITE_ID` (`website_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='On Sale Label Rules To Websites Relations';");
} catch (Exception $exc) {
    
}

$installer->endSetup();
