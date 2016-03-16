<?php

$installer = $this;
$installer->startSetup();

// Add va_number column to sales_flat_order_payment
try {
    $installer->run("
        ALTER TABLE sales_flat_order_payment ADD COLUMN va_number VARCHAR(20);
    ");
} catch (Exception $e) {
    Mage::logException($e);
}

$installer->endSetup();