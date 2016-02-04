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
	CREATE TABLE  `{$this->getTable('netsuite_adjustment_inventory')}` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
    `internal_netsuite_id` VARCHAR( 50 ) NOT NULL ,
    `last_update_at` DATETIME NOT NULL ,
    `quantity` INT NOT NULL ,
    UNIQUE (
    `internal_netsuite_id`
    )
    ) ENGINE = INNODB;
");

$installer->endSetup();