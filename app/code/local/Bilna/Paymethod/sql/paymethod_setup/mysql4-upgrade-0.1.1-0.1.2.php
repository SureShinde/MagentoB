<?php
$installer = $this;
$installer->startSetup();
$installer->run("
    ALTER TABLE `{$installer->getTable('sales/quote_item')}`
        ADD COLUMN `installment` tinyint(1) DEFAULT 0 COMMENT '0->false;1->true',
        ADD COLUMN `installment_method` varchar(10) DEFAULT NULL COMMENT 'manual,automatic';
    ALTER TABLE `{$installer->getTable('sales/order_item')}`
        ADD COLUMN `installment` tinyint(1) DEFAULT 0 COMMENT '0->false;1->true',
        ADD COLUMN `installment_method` varchar(10) DEFAULT NULL COMMENT 'manual,automatic';
");
$installer->endSetup();
