<?php
/**
 *
 * @category   Bilna
 * @package    Bilna_Wrappinggiftevent
 * @version    
 * @copyright  www.bilna.com
 * @license    
 */


$installer = $this;

$installer->startSetup();

$installer->run("
CREATE TABLE `wrappinggiftevent_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `store_id` int(11) DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL,
  `wrapping_type` char(100) DEFAULT NULL,
  `wrapping_price` decimal(13,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

CREATE TABLE `wrappinggiftevent_quote` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `store_id` int(11) DEFAULT NULL,
  `quote_id` int(11) DEFAULT NULL,
  `wrapping_type` char(100) DEFAULT NULL,
  `wrapping_price` decimal(13,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

CREATE TABLE `wrapping_gift_event` (
  `id` tinyint(3) NOT NULL AUTO_INCREMENT,
  `wrapping_name` char(200) NOT NULL,
  `wrapping_price` double(13,0) NOT NULL,
  `wrapping_desc` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1 COMMENT='wrapping gift event'
");

$installer->endSetup();