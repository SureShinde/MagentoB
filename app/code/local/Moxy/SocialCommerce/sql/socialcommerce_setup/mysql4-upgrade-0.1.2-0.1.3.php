<?php

$installer = $this;
$installer->startSetup();

$installer->run("ALTER TABLE `moxy_socialcommerce_profile` ADD `cloud_avatar` " . 'varchar(128)' . " COLLATE 'utf8_general_ci' NULL AFTER `avatar`;");

$installer->endSetup();
