<?php
$installer = $this;
$installer->startSetup();
$installer->run("CREATE TABLE `{$this->getTable('history')}` (
  `history_id` int(10) NOT NULL AUTO_INCREMENT,
  `order_id` int(10) NOT NULL,
  `customer_id` int(10) NOT NULL,
  `coupon_id` int(10) NOT NULL,
  `rule_id` int(10) NOT NULL,
  `mobile_number` varchar(30) NOT NULL,
  PRIMARY KEY (`history_id`),
  KEY `search` (`customer_id`,`coupon_id`,`mobile_number`),
  KEY `search2` (`customer_id`,`rule_id`,`mobile_number`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

");
$installer->endSetup();
