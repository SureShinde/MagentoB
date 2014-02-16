<?php
$installer = $this;

$installer->startSetup();

$installer->run("
 
-- DROP TABLE IF EXISTS {$this->getTable('stockmonitor/stockmovement')};
CREATE TABLE {$this->getTable('stockmonitor/stockmovement')} (
  `movement_id` int(11) unsigned NOT NULL auto_increment,
  `product_id` int(11) unsigned NOT NULL,
  `increment_id` int(11) unsigned NOT NULL,
  `order_id` int(11) unsigned NOT NULL,
  `qty_change` varchar(255) NOT NULL,
  `action_performed` varchar(255) NOT NULL,
  `updated_at` timestamp NOT NULL,
  PRIMARY KEY (`movement_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
 
    ");

$installer->endSetup();