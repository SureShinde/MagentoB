<?php

$installer = $this;

$installer->startSetup();

$installer->run("

ALTER TABLE `bilna_formbuilder_form` ADD COLUMN `button_text` VARCHAR(100) NULL AFTER `status`;

");

$installer->endSetup();
