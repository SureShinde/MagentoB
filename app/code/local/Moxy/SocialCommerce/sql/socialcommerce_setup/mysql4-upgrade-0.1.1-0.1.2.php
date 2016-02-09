<?php

$installer = $this;
$installer->startSetup();

$installer->run("ALTER TABLE `wishlist` ADD `cloud_cover` " . 'varchar(128)' . " COLLATE 'utf8_general_ci' NULL;");

$installer->endSetup();
