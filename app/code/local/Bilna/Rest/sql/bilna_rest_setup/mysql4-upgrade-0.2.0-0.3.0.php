<?php

$installer = $this;
$installer->startSetup();

try {
    $installer->run("
        ALTER TABLE `api_product_flat_1`
        ADD `sales` decimal(12,4) NOT NULL DEFAULT '0.0000' COMMENT 'Total sales'
        AFTER `images`;
    ");
}
catch (Exception $ex) {
    Mage::logException($ex);
}

$installer->endSetup();
 