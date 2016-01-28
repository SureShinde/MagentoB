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
	CREATE TABLE  `{$this->getTable('netsuite_api_log')}` (
		`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
		`request` LONGTEXT NOT NULL ,
		`response` LONGTEXT NOT NULL ,
		`call_date` DATETIME NOT NULL
	) ENGINE = INNODB;
");

$installer->endSetup();