<?php

$installer = $this;
$installer->startSetup();

$installer->run(
	"CREATE TABLE `sales_order_daily_count` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`sales_date` DATE,
		`sales_count` INT,
		PRIMARY KEY (`id`)
	) ENGINE=InnoDB"
);

$installer->endSetup();