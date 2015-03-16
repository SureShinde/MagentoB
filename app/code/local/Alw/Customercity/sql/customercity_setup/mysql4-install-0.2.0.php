<?php
   $installer = $this;
     
    $installer->startSetup();
     
    $installer->run("
     
    DROP TABLE IF EXISTS {$this->getTable('directory_country_city')};
    CREATE TABLE {$this->getTable('directory_country_city')} (
       `id` int(11) unsigned NOT NULL auto_increment,
	   `city` varchar(50) NOT NULL default '',
	   `state` varchar(50) NOT NULL default '',
	   `country` varchar(50) NOT NULL default '',
       PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
   ");
     
    $installer->endSetup();
	
	