<?php

$installer = $this;
$installer->startSetup();

$installer->run("ALTER TABLE `wishlist` ADD `cover` " . 'varchar(256)' . " COLLATE 'utf8_general_ci' NULL;");

$installer->endSetup();
