<?php

$installer = $this;

$installer->startSetup();

$installer->run("

ALTER TABLE `bilna_formbuilder_form` 
	ADD COLUMN `success_redirect` INT(1) NULL  AFTER `success_message` ,
	ADD COLUMN `url_success` VARCHAR(100) NULL  AFTER `url` ;		
");

$installer->endSetup();