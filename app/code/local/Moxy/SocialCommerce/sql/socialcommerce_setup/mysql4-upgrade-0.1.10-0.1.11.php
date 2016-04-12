<?php

$installer = $this;
$installer->startSetup();

$installer->run("ALTER TABLE `wishlist` ADD `categories` " . 'varchar(255)' . " COLLATE 'utf8_general_ci' NULL;");

$installer->endSetup();
