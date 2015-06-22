<?php

$installer = $this;

$installer->startSetup();

$installer->run("

ALTER TABLE `bilna_formbuilder_input` ADD COLUMN `validation` VARCHAR(150) NULL AFTER `order`;

");

$installer->endSetup();
