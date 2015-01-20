<?php
$installer = $this;
$installer->startSetup();
$installer->run("
    ALTER TABLE `bilna_formbuilder_input` 
        ADD COLUMN `dbtype` VARCHAR(30) NULL AFTER `type`;
");
$installer->endSetup();
