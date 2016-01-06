<?php
$installer = $this;
$installer->startSetup();
$installer->run("
CREATE TABLE IF NOT EXISTS `bilna_fraud_order` (
  `log_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Log Id',
  `order_id` int(10) unsigned NOT NULL COMMENT 'Order Id',
  `entity_id` int(10) unsigned NOT NULL COMMENT 'Entity Id',
  `customer_name` varchar(255) NOT NULL COMMENT 'Customer Name',
  `email` varchar(32) NOT NULL COMMENT 'Customer Email',
  `shipping_address` varchar(255) NOT NULL COMMENT 'Shipping Address',
  `billing_address` varchar(255) NOT NULL COMMENT 'Billing Address',
  `grand_total` int(10) unsigned NOT NULL COMMENT 'Grand Total',
  `payment_method` varchar(50) NOT NULL COMMENT 'Payment Method Used',
  `coupon_code` varchar(30) NOT NULL COMMENT 'Coupon Code Used',
  `rule_id` varchar(25) NOT NULL COMMENT 'Rule Id',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'Created At',
  PRIMARY KEY (`log_id`)
);
		
");
$installer->endSetup();
