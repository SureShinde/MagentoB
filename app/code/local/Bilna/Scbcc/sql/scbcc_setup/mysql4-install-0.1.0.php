<?php
$installer = $this;
$installer->startSetup();
$installer->run("    
    CREATE TABLE IF NOT EXISTS `bin_code` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `code` int(11) NOT NULL,
        `platform` varchar(64) NOT NULL,
        `issuer` varchar(32) NOT NULL,
        `name` varchar(128) NOT NULL,
        PRIMARY KEY (`id`)
    );

    INSERT INTO bin_code VALUES(NULL,'493499','Visa','SC','Visa Infinite');
    INSERT INTO bin_code VALUES(NULL,'493498','Visa','SC','Visa Black Platinum');
    INSERT INTO bin_code VALUES(NULL,'493497','Visa','SC','Visa Business Platinum');
    INSERT INTO bin_code VALUES(NULL,'493496','Visa','SC','Visa Business Regular');
    INSERT INTO bin_code VALUES(NULL,'451197','Visa','SC','Visa Gold');
    INSERT INTO bin_code VALUES(NULL,'451196','Visa','SC','Visa Classic');
    INSERT INTO bin_code VALUES(NULL,'552339','Master','SC','Master World');
    INSERT INTO bin_code VALUES(NULL,'514934','Master','SC','Master Platinum');
    INSERT INTO bin_code VALUES(NULL,'514934','Master','SC','Master Titanium');
    INSERT INTO bin_code VALUES(NULL,'514934','Master','SC','Master JustOne');
    INSERT INTO bin_code VALUES(NULL,'514904','Master','SC','Master Gold');
    INSERT INTO bin_code VALUES(NULL,'544305','Master','SC','Master Classic');

");
$installer->endSetup();