<?php
$installer = $this;
$installer->startSetup();

$installer->run("

ALTER TABLE {$this->getTable('blog/lcat')} ADD `parent_id` INT( 11 ) NOT NULL DEFAULT 0 AFTER `cat_id` ;
");

$installer->endSetup();