<?php

$installer = $this;
$installer->startSetup();

$installer->run("ALTER TABLE `wishlist` ADD `view` " . 'varchar(5)' . " COLLATE 'utf8_general_ci' NOT NULL DEFAULT '1';");

$installer->endSetup();
