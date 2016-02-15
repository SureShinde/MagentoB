<?php

$installer = $this;
$installer->startSetup();

try {
    $installer->run("
        ALTER TABLE `api_product_flat_1` DROP COLUMN `review`;
        ALTER TABLE `api_product_flat_1` CHANGE `sales` `sales_price` decimal(12,4) NOT NULL DEFAULT '0.0000' COMMENT 'Total sales';
    ");
}
catch (Exception $ex) {
    Mage::logException($ex);
}

$installer->endSetup();
 