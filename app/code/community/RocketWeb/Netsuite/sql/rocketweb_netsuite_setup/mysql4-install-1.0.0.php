<?php
/**
 * Rocket Web Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is available through the world-wide-web at this URL:
 * http://www.rocketweb.com/RW-LICENSE.txt
 *
 * @category   RocketWeb
 * @package    RocketWeb_Netsuite
 * @copyright  Copyright (c) 2013 RocketWeb (http://www.rocketweb.com)
 * @author     Rocket Web Inc.
 * @license    http://www.rocketweb.com/RW-LICENSE.txt
 */
$installer = $this;

$installer->startSetup();

$installer->run("
		CREATE TABLE IF NOT EXISTS `{$this->getTable('message')}` (
			  `message_id` bigint(20) unsigned NOT NULL auto_increment,
			  `queue_id` int(10) unsigned NOT NULL,
			  `handle` char(32) default NULL,
			  `body` varchar(8192) NOT NULL,
			  `md5` char(32) NOT NULL,
			  `timeout` decimal(14,4) unsigned default NULL,
			  `created` int(10) unsigned NOT NULL,
			  PRIMARY KEY  (`message_id`),
			  UNIQUE KEY `message_handle` (`handle`),
			  KEY `message_queueid` (`queue_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");


$installer->run("
	CREATE TABLE IF NOT EXISTS `{$this->getTable('queue')}` (
		`queue_id` int(10) unsigned NOT NULL auto_increment,
		`queue_name` varchar(100) NOT NULL,
		`timeout` smallint(5) unsigned NOT NULL default '30',
		PRIMARY KEY  (`queue_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->run("
	ALTER TABLE `{$this->getTable('message')}`
		ADD CONSTRAINT `message_ibfk_1` FOREIGN KEY (`queue_id`) REFERENCES `{$this->getTable('queue')}` (`queue_id`) ON DELETE CASCADE ON UPDATE CASCADE;
");

$installer->endSetup();