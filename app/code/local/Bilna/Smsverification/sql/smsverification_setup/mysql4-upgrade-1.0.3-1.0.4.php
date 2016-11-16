<?php
$installer = $this;
$installer->startSetup();
$installer->run("
     CREATE TABLE IF NOT EXISTS `{$this->getTable('sms_dr')}` (
            `sms_id` bigint(20) unsigned NOT NULL auto_increment,
            `dr` varchar(50) NOT NULL,
            `order_id` varchar(50) NOT NULL,
            `msisdn` varchar(50) DEFAULT NULL,
            `created_at` datetime NOT NULL default '0000-00-00 00:00:00',
            PRIMARY KEY  (`sms_id`),
            KEY `dr` (`dr`)
     ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");
$installer->endSetup();
