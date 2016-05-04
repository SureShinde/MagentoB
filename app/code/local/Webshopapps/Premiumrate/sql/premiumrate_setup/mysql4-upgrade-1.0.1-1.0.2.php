<?php

$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE  `{$this->getTable('shipping_premiumrate')}` ADD COLUMN `is_import` INT
");

$installer->endSetup();