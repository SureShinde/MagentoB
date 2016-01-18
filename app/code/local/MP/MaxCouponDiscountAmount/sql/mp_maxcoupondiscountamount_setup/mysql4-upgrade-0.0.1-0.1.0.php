<?php

$installer = $this;

$installer->startSetup();

$sql = "ALTER TABLE `{$installer->getTable('salesrule')}` ADD `max_discount_amount` DECIMAL(12,4) NOT NULL DEFAULT '0.0000' AFTER `discount_amount`;";

$installer->getConnection()->query($sql);

$installer->endSetup();