<?php

$installer = $this;

$installer->startSetup();

$installer->run("

ALTER TABLE `bilna_formbuilder_form` 
			ADD COLUMN `success_message` VARCHAR(256) NULL AFTER `static_info`,
			ADD COLUMN `sent_email` TINYINT(3) NULL AFTER `active_to`, 
			ADD COLUMN `email_id` INT(5) NULL AFTER `sent_email`;
");

$installer->endSetup();


