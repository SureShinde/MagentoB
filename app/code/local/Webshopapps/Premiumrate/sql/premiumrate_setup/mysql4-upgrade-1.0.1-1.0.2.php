<?php

$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE  `{$this->getTable('shipping_premiumrate')}` ADD COLUMN `is_import` INT
");

$installer->run("
ALTER TABLE  `{$this->getTable('shipping_premiumrate')}` ADD COLUMN `delivery_days_from` INT
");

$installer->run("
ALTER TABLE  `{$this->getTable('shipping_premiumrate')}` ADD COLUMN `delivery_days_to` INT
");

$installer->endSetup();