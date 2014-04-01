<?php

$installer = $this;
$installer->startSetup();

$installer->run("
DROP TABLE IF EXISTS {$this->getTable('icube_category_generator')};
CREATE TABLE {$this->getTable('icube_category_generator')}
(
`id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`name` TINYTEXT NOT NULL ,
`description` TEXT NOT NULL ,
`is_active` TINYINT( 1 ) NOT NULL DEFAULT '1' ,
`category_data` TEXT NOT NULL ,
`conditions_serialized` TEXT NOT NULL
)ENGINE = MYISAM;");

$installer->endSetup();