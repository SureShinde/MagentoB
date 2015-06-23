<?php

$installer = $this;

$installer->startSetup();

$installer->run("
ALTER TABLE `icube_category_generator` ADD COLUMN `is_onsale` TINYINT(1) NOT NULL DEFAULT 0  AFTER `conditions_serialized` , ADD COLUMN `is_new` TINYINT(1) NOT NULL DEFAULT 0  AFTER `is_onsale` ;
");

$installer->endSetup();