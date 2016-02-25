<?php

$installer = $this;
$installer->startSetup();

$installer->run("ALTER TABLE `{$this->getTable('sales_flat_order_item')}` ADD COLUMN `express_shipping` INT(11) DEFAULT 0");

$installer->endSetup();