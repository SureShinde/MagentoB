<?php

$installer = $this;

$installer->startSetup();

$installer->run("
CREATE TABLE `payment_base_shipping` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`shipping` varchar(64) NOT NULL,
	`exclude_payment` varchar(256) NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB
");

$installer->endSetup();