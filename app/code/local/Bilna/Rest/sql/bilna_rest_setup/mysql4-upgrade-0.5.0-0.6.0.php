<?php

$installer = $this;
$installer->startSetup();

try {
    $installer->run(
        "ALTER TABLE `cataloginventory_stock_item`
        ADD `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;"
    );
}
catch (Exception $ex) {
    Mage::logException($ex);
}

$installer->endSetup();
 