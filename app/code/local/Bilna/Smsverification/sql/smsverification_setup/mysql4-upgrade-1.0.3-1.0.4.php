<?php
$installer = $this;
$installer->startSetup();
$installer->run("
     CREATE TABLE IF NOT EXISTS `{$this->getTable('sms_dr')}` (
            `sms_id` bigint(20) unsigned NOT NULL auto_increment,
            `code` varchar(50) NOT NULL,
            `order_id` int(10) unsigned NOT NULL,
            `msisdn` varchar(50) DEFAULT NULL,
            `created_at` timestamp NOT NULL default CURRENT_TIMESTAMP,
            PRIMARY KEY  (`sms_id`),
            UNIQUE KEY `code` (`code`)
     ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");
$installer->endSetup();
