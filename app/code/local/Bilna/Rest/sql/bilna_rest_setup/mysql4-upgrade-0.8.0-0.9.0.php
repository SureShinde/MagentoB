<?php

$installer = $this;
$installer->startSetup();

try {
    $installer->run("
        ALTER TABLE `api_product_flat_1`
        ADD `attributes` text
        AFTER `detailed_info`;
    ");
}
catch (Exception $ex) {
    Mage::logException($ex);
}

$installer->endSetup();

