<?php
$installer = $this;
$installer->startSetup();
$installer->run("
    CREATE TABLE IF NOT EXISTS `bilna_formbuilder_lastupdate` (
        `table_name` varchar(50) NOT NULL,
        `last_id` int(11) NOT NULL,
        PRIMARY KEY (`table_name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
");
$installer->endSetup();
