<?php
$installer = $this;
$installer->startSetup();
$installer->run("
CREATE TABLE IF NOT EXISTS `bilna_unique_coupon_log` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `coupon_code` varchar(255) NOT NULL,
  `quote_id` int(10) unsigned NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `coupon_code` (`coupon_code`)
);
");
$installer->endSetup();
