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
ALTER TABLE  `{$this->getTable('message')}` ADD  `processing` TINYINT( 2 ) UNSIGNED NOT NULL DEFAULT  '0' COMMENT  'When it is processed, the value is 1',
ADD INDEX (  `processing` )
");

$installer->endSetup();