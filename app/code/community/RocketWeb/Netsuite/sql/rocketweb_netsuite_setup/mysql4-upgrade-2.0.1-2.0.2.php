<?php
$installer = $this;
$installer->startSetup();
$installer->run("
    CREATE TABLE IF NOT EXISTS `{$this->getTable('netsuite_product_cost')}` (
        `id` int(11) unsigned NOT NULL auto_increment,
        `product_id` int(11) unsigned NOT NULL,
        `cost` int(11) default NULL,
        `expected_cost` int(11) default NULL,
        `event_cost` int(11) default NULL,
        `event_start_date` date default NULL,
        `event_end_date` date default NULL,
        `netsuite_internal_id` int(11) default NULL,
        PRIMARY KEY  (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");
$installer->endSetup();
