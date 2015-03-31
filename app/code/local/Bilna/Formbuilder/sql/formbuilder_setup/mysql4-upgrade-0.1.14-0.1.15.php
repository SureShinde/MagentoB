<?php
$installer = $this;
$installer->startSetup();
$installer->run("
    ALTER TABLE `bilna_formbuilder_input`
        ADD COLUMN `validation` varchar(100) DEFAULT NULL AFTER `helper_message`;
");
$installer->endSetup();
