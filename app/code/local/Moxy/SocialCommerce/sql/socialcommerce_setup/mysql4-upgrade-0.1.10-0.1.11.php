<?php

$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE `wishlist` ADD `categories` " . 'varchar(255)' . " COLLATE 'utf8_general_ci' NULL;

CREATE TABLE `{$installer->getTable('socialcommerce/customercollection')}` (
    `map_id` int(11) unsigned NOT NULL auto_increment,
    `wishlist_id` int(11) unsigned NOT NULL,
    `collection_category_id` int(11) unsigned NOT NULL,
    PRIMARY KEY (`map_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

ALTER TABLE `{$installer->getTable('socialcommerce/collectioncategory')}` ADD `show_in_coll_page` " . 'BOOLEAN' . " COLLATE 'utf8_general_ci' NOT NULL DEFAULT 0;

ALTER TABLE `{$installer->getTable('socialcommerce/collectioncategory')}` ADD `is_active` " . 'BOOLEAN' . " COLLATE 'utf8_general_ci' NOT NULL DEFAULT 0;
ALTER TABLE `{$installer->getTable('socialcommerce/collectioncategory')}` ADD `sort_no` " . 'int(11)' . " unsigned COLLATE 'utf8_general_ci' NOT NULL DEFAULT 0;

ALTER TABLE `{$installer->getTable('socialcommerce/collectioncategory')}` ADD `url` " . 'varchar(255)' . " COLLATE 'utf8_general_ci' NULL;
");

$installer->endSetup();
