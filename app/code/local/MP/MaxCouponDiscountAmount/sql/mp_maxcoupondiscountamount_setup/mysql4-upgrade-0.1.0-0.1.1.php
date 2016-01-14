<?php

$installer = $this;
$installer->startSetup();

try {
	$sql = "ALTER TABLE `sales_flat_quote` ADD `max_discount_amount` DECIMAL(12,4) NOT NULL DEFAULT '0.0000' AFTER `base_subtotal`;";
    $installer->run($sql);
} catch (Exception $ex) {
    Mage::logException($ex);
}

$installer->endSetup();