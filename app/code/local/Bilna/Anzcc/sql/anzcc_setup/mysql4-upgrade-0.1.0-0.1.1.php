<?php

$installer = $this;

$installer->startSetup();

$installer->run("

DROP TABLE IF EXISTS `bin_code`;
CREATE TABLE `bin_code` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` int(11) NOT NULL,
  `platform` varchar(64) NOT NULL,
  `issuer` varchar(32) NOT NULL,
  `name` varchar(128) NOT NULL,
  PRIMARY KEY (`id`)
);

INSERT INTO bin_code VALUES(NULL,'437451','Visa','ANZ','ANZ SPB Infinite');
INSERT INTO bin_code VALUES(NULL,'522846','MasterCard','ANZ','ANZ Black');
INSERT INTO bin_code VALUES(NULL,'541616','MasterCard','ANZ','ANZ Platinum');
INSERT INTO bin_code VALUES(NULL,'541070','MasterCard','ANZ','ANZ Gold');
INSERT INTO bin_code VALUES(NULL,'541069','MasterCard','ANZ','ANZ Classic');
INSERT INTO bin_code VALUES(NULL,'430981','Visa','ANZ','ANZ Platinum');
INSERT INTO bin_code VALUES(NULL,'415736','Visa','ANZ','ANZ Gold');
INSERT INTO bin_code VALUES(NULL,'415735','Visa','ANZ','ANZ Classic');
INSERT INTO bin_code VALUES(NULL,'415735','Visa','ANZ','ANZ Kartu Cicilan');
INSERT INTO bin_code VALUES(NULL,'458769','Visa','ANZ','ANZ Femme Platinum');
INSERT INTO bin_code VALUES(NULL,'405542','Visa','ANZ','ANZ Femme');
INSERT INTO bin_code VALUES(NULL,'437456','Visa','ANZ','ANZ Travel Signature');
INSERT INTO bin_code VALUES(NULL,'437450','Visa','ANZ','ANZ Travel Platinum');
INSERT INTO bin_code VALUES(NULL,'528912','MasterCard','ANZ','RPB (ex-RBS)');
INSERT INTO bin_code VALUES(NULL,'528912','MasterCard','ANZ','Platinum (ex-RBS)');
INSERT INTO bin_code VALUES(NULL,'512021','MasterCard','ANZ','Gold (ex-RBS)');
INSERT INTO bin_code VALUES(NULL,'525644','MasterCard','ANZ','Classic (ex-RBS)');
INSERT INTO bin_code VALUES(NULL,'512422','MasterCard','ANZ','iTravel (ex-RBS)');
INSERT INTO bin_code VALUES(NULL,'510217','MasterCard','ANZ','iPay (ex-RBS)');
INSERT INTO bin_code VALUES(NULL,'510249','MasterCard','ANZ','iCash (ex-RBS)');
		
");

$installer->endSetup();