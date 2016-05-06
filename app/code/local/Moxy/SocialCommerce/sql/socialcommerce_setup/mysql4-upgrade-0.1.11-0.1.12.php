<?php

$installer = $this;
$installer->startSetup();

$installer->run("ALTER TABLE `wishlist` ADD `editor_flag` " . 'smallint(6)' . " COLLATE 'utf8_general_ci' NULL;");

$installer->endSetup();
