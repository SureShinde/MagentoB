<?php
$installer = $this;
$installer->startSetup();
$installer->run("
CREATE TABLE IF NOT EXISTS `bilna_ccp_product_scoring` (
  `product_id` int(10) unsigned NOT NULL COMMENT 'Product Id',
  `sales` int(10) unsigned NOT NULL COMMENT 'Sales',
  `sales_rank` int(10) unsigned NOT NULL COMMENT 'Sales Ranking from all products',
  `inventory` int(10) unsigned NOT NULL COMMENT 'Product Inventory',
  `inventory_rank` int(10) unsigned NOT NULL COMMENT 'Product Inventory Ranking from all products',
  `score` decimal(10,2) NOT NULL COMMENT 'Product Scoring',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'Created At',
  PRIMARY KEY (`product_id`)
);
		
");
$installer->endSetup();
