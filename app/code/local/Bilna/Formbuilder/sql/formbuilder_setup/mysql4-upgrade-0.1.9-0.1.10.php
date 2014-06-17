<?php

$installer = $this;

$installer->startSetup();

$installer->run("

ALTER TABLE `bilna_formbuilder_form` 
	ADD COLUMN `class` VARCHAR(128) NULL  AFTER `active_to` ,
	ADD COLUMN `button_text` VARCHAR(32) NULL  AFTER `class` ,
	ADD COLUMN `termsconditions` TEXT NULL  AFTER `url` , 
	ADD COLUMN `freeproducts` VARCHAR(256) NULL  AFTER `termsconditions`,
	ADD COLUMN `static_failed` VARCHAR(50) NULL  AFTER `static_success` , 
	ADD COLUMN `force_flow` INT(1) NULL DEFAULT 0  AFTER `static_failed` ;
		
");

$installer->endSetup();
