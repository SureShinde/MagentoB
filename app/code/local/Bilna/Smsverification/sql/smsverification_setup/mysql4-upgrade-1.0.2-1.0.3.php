<?php
$installer = $this;
$installer->startSetup();
$installer->run("
     CREATE TABLE IF NOT EXISTS `{$this->getTable('otp_failed')}` (
            `otp_id` bigint(20) unsigned NOT NULL auto_increment,
            `customer_id` varchar(50) NOT NULL,
            `otp_code` varchar(50) NOT NULL,
            `created_at` datetime NOT NULL default '0000-00-00 00:00:00',
            PRIMARY KEY  (`otp_id`),
            KEY `customer` (`customer_id`)
     ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");
$installer->endSetup();
