<?php
$installer = $this;
$installer->startSetup();
$installer->run("
    ALTER TABLE `{$installer->getTable('sales/quote_payment')}` ADD `anzcc_bins` VARCHAR(6) DEFAULT NULL;
    ALTER TABLE `{$installer->getTable('sales/order_payment')}` ADD `anzcc_bins` VARCHAR(6) DEFAULT NULL;
");
$installer->endSetup();
