<?php

$installer = $this;
$installer->startSetup();

try {
    $installer->run("
        ALTER TABLE `api_product_flat_1`
        ADD `in_stock` tinyint(1) NOT NULL DEFAULT '0'
        AFTER `sales_price`;
    ");
}
catch (Exception $ex) {
    Mage::logException($ex);
}

$installer->endSetup();

