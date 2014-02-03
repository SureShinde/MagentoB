<?php

$installer = $this;

$installer->startSetup();

$installer->run("
	ALTER TABLE `shipping_premiumrate` DROP COLUMN `shipping_type` ;
	ALTER TABLE shipping_premiumrate ADD COLUMN delivery_id int(11) DEFAULT 0;
	
	DROP TABLE IF EXISTS `payment_base_shipping`;
	CREATE TABLE `payment_base_shipping` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`delivery` varchar(256) NOT NULL,
		`flow` varchar(64) NOT NULL,
		`exclude_payment` varchar(256) NOT NULL,
		PRIMARY KEY (`id`)
	) ENGINE=InnoDB;
		
	INSERT INTO `payment_base_shipping` (`id`, `delivery`, `flow`, `exclude_payment`) VALUES ('0', 'standard', 'shipping', 'cod');
	INSERT INTO `payment_base_shipping` (`id`, `delivery`, `flow`, `exclude_payment`) VALUES ('1', 'no_prepayment', 'cod', 'klikpay, klikbca, veritrans, transferbca, transferbni, transfermandiri');
	INSERT INTO `payment_base_shipping` (`id`, `delivery`, `flow`, `exclude_payment`) VALUES ('2', 'express', 'shipping', 'cod');
	INSERT INTO `payment_base_shipping` (`id`, `delivery`, `flow`, `exclude_payment`) VALUES ('3', 'super_express', 'shipping', 'cod');
");

$installer->endSetup();