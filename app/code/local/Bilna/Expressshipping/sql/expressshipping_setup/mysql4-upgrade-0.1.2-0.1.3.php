<?php

$installer = $this;
$installer->startSetup();

try {
	$installer->run(" 
		ALTER TABLE `bilna_live`.`sales_order_daily_count` 
		CHANGE COLUMN `sales_date` `sales_datetime_from` DATETIME NULL DEFAULT NULL ,
		ADD COLUMN `sales_datetime_to` DATETIME NULL;
		");
} catch (Exception $e) {
	Mage::logException($e);
}

$installer->endSetup();