<?php
$installer = $this;
$installer->startSetup();
$installer->run("
     CREATE TABLE IF NOT EXISTS `{$this->getTable('otp_list')}` (
            `otp_id` bigint(20) unsigned NOT NULL auto_increment,
            `msisdn` varchar(50) NOT NULL,
            `otp_code` varchar(50) NOT NULL,
            `type` INT(3) NOT NULL default 0,
            `created_at` datetime NOT NULL default '0000-00-00 00:00:00',
            PRIMARY KEY  (`otp_id`),
            UNIQUE KEY `otp_to_msisdn` (`otp_code`,`msisdn`,`type`)
     ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");
$installer->endSetup();
