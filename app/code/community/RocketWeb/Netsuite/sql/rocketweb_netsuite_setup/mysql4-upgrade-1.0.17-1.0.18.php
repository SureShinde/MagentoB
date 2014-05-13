<?php
$installer = $this;

$installer->startSetup();

$installer->run("ALTER TABLE  `{$this->getTable('netsuite_changelog')}` CHANGE  `comment`  `comment` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL");
$installer->run("ALTER TABLE  `{$this->getTable('netsuite_changelog')}` DROP INDEX  `action` ,ADD INDEX  `action` (  `action` )");
$installer->run("ALTER TABLE  `{$this->getTable('netsuite_changelog')}` ADD INDEX (  `created_date` )");

$installer->endSetup();