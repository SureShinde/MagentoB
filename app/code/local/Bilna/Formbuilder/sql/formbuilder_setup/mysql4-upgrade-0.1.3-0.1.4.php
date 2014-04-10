<?php

$installer = $this;

$installer->startSetup();

$installer->run("

ALTER TABLE `bilna_formbuilder_input` CHANGE COLUMN `order` `order` INT NOT NULL  ;
ALTER TABLE `bilna_formbuilder_input` 
		CHANGE COLUMN `name` `name` VARCHAR(64) NOT NULL  , 
		CHANGE COLUMN `group` `group` VARCHAR(64) NOT NULL  , 
		CHANGE COLUMN `title` `title` VARCHAR(64) NULL  , 
		CHANGE COLUMN `type` `type` VARCHAR(64) NOT NULL  , 
		ADD COLUMN `value` VARCHAR(64) NULL  AFTER `order` , 
		ADD COLUMN `helper_message` VARCHAR(256) NULL  AFTER `value` ;

ALTER TABLE `bilna_formbuilder_input` 
		CHANGE COLUMN `value` `value` VARCHAR(64) NULL DEFAULT NULL  AFTER `title` , 
		CHANGE COLUMN `helper_message` `helper_message` VARCHAR(256) NULL DEFAULT NULL  AFTER `value` ;

");

$installer->endSetup();
