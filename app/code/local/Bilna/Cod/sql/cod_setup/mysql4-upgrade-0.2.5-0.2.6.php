<?php

$installer = $this;
$installer->startSetup();

try {
    $installer->run("ALTER TABLE `{$this->getTable('sales_flat_order_item')}` ADD COLUMN `cod` INT(11) DEFAULT 0");
    $installer->run("ALTER TABLE `{$this->getTable('sales_flat_quote_item')}` ADD COLUMN `cod` INT(11) DEFAULT 0");
} catch (Exception $e) {
    Mage::logException($e);
}

$installer->endSetup();