<?php
$installer = $this;
$installer->startSetup();
$installer->run("
    CREATE TABLE `blacklist_email` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `customer_id` int(11) NOT NULL,
        `customer_email` varchar(255) NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
");
$installer->endSetup();
	 