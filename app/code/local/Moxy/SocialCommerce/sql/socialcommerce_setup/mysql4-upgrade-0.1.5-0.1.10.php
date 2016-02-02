<?php

$installer = $this;
$installer->startSetup();

$installer->run("ALTER TABLE `wishlist` ADD `name` " . 'varchar(255)' . " COLLATE 'utf8_general_ci' NULL; ALTER TABLE `wishlist` ADD `visibility` " . 'smallint(6)' . " COLLATE 'utf8_general_ci' NOT NULL DEFAULT '0'; ALTER TABLE `wishlist` DROP INDEX `UNQ_WISHLIST_CUSTOMER_ID` , ADD INDEX `IDX_WISHLIST_CUSTOMER_ID` (`customer_id` ". "ASC); ALTER TABLE `core_url_rewrite` ADD `identifier` " . 'varchar(255)' . " COLLATE 'utf8_general_ci' NULL;");

$installer->endSetup();
