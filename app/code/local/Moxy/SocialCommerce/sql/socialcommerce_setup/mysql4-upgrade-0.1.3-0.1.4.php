<?php

$installer = $this;
$installer->startSetup();

$installer->run("ALTER TABLE `wishlist` ADD `desc` " . 'varchar(250)' . " COLLATE 'utf8_general_ci' NULL;");

$installer->endSetup();
