<?php

$installer = $this;
$installer->startSetup();

$installer->run("
    CREATE TABLE `{$installer->getTable('socialcommerce/profile')}` (
        `entity_id` int(11) unsigned NOT NULL auto_increment,
        `customer_id` int(11) unsigned NOT NULL,
        `username` varchar(32) NOT NULL,
        `about` varchar(254) NULL,
        `location` varchar(32) NULL,
        `avatar` varchar(256) NULL,
        `wishlist` smallint(1) unsigned NOT NULL default '0',
        `status` smallint(1) unsigned NOT NULL default '0',
        `temporary` smallint(1) unsigned NOT NULL default '1',
        PRIMARY KEY (`entity_id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8;
");

$installer->endSetup();
