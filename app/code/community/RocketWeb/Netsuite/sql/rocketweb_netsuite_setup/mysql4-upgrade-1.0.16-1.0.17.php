<?php
$installer = $this;

$installer->startSetup();

$installer->run("
CREATE TABLE  `{$this->getTable('netsuite_changelog')}` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
    `action` VARCHAR( 15 ) NOT NULL ,
    `created_date` DATETIME NOT NULL ,
    `internal_id` VARCHAR( 255 ) NOT NULL ,
    `comment` VARCHAR( 255 ) NOT NULL ,
    INDEX (  `action` ,  `created_date` )
)
");

$installer->endSetup();