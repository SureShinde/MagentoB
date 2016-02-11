<?php

$installer = $this;

$installer->startSetup();

$sql = "ALTER TABLE `{$installer->getTable('sales/quote')}` ADD `max_discount_amount` DECIMAL(12,4) NOT NULL DEFAULT '0.0000' AFTER `base_subtotal`;";

$installer->getConnection()->query($sql);

$installer->endSetup();