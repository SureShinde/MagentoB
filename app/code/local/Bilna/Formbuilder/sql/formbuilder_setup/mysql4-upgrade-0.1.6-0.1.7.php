<?php

$installer = $this;

$installer->startSetup();

$installer->run("

ALTER TABLE `bilna_formbuilder_input` 
			ADD COLUMN `value` VARCHAR(100) NULL AFTER `title`,
			ADD COLUMN `helper_message` VARCHAR(256) NULL AFTER `value`,
			MODIFY `order` int(11) NOT NULL;
");

$installer->endSetup();


