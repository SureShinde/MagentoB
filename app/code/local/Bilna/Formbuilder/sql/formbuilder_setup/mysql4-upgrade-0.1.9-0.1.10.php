<?php

$installer = $this;

$installer->startSetup();

$installer->run("

ALTER TABLE `bilna_formbuilder_form` 
	ADD COLUMN `class` VARCHAR(128) NULL  AFTER `active_to` ,
	ADD COLUMN `button_text` VARCHAR(32) NULL  AFTER `class` ,
	ADD COLUMN `termsconditions` TEXT NULL  AFTER `url` , 
	ADD COLUMN `freeproducts` VARCHAR(256) NULL  AFTER `termsconditions` ;

");

$installer->endSetup();
