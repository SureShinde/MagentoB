<?php
$installer = $this;
$installer->startSetup();
$installer->run("
    ALTER TABLE `bilna_formbuilder_form`
        ADD COLUMN `email_share_apps` tinyint(1) NOT NULL DEFAULT '0',
        ADD COLUMN `social_title` varchar(100) DEFAULT NULL,
        ADD COLUMN `social_desc` varchar(256) DEFAULT NULL,
        ADD COLUMN `social_image` varchar(100) DEFAULT NULL,
        ADD COLUMN `fue` int(11) NULL DEFAULT '0';
");
$installer->endSetup();
