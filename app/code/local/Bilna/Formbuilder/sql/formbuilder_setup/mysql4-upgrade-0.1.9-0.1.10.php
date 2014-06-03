<?php

$installer = $this;

$installer->startSetup();

$installer->run("

ALTER TABLE `bilna_formbuilder_form` ADD COLUMN `class` VARCHAR(128) NULL  AFTER `active_to` ;
ALTER TABLE `bilna_formbuilder_form` ADD COLUMN `button_text` VARCHAR(32) NULL  AFTER `class` ;

");

$installer->endSetup();
