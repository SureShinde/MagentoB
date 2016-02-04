<?php

$installer = $this;

$installer->startSetup();

$installer->run("
ALTER TABLE `payment_base_shipping` CHANGE COLUMN `shipping` `shipping_type` VARCHAR(32) NOT NULL  , ADD COLUMN `delivery_type` VARCHAR(128) NOT NULL  AFTER `shipping_type` ;
");

$installer->endSetup();