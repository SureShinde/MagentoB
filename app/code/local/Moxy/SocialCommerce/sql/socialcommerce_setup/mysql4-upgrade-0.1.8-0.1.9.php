<?php

$installer = $this;
$installer->startSetup();

$installer->run("ALTER TABLE `wishlist` ADD `created_at` " . 'TIMESTAMP' . " COLLATE 'utf8_general_ci' NULL DEFAULT CURRENT_TIMESTAMP;");

$installer->endSetup();
