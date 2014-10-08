<?php
$installer = $this;
$installer->startSetup();
$installer->run("
    INSERT INTO `bin_code` (`code`,`issuer`,`platform`,`name`)
    VALUES
	('420193','megavisa','Visa','Bank Mega'),
	('421407','megavisa','Visa','Bank Mega'),
	('421408','megavisa','Visa','Bank Mega'),
	('457508','megavisa','Visa','Bank Mega'),
	('458785','megavisa','Visa','Bank Mega'),
	('464933','megavisa','Visa','Bank Mega'),
	('472670','megavisa','Visa','Bank Mega'),
	('524261','megamc','MasterCard','Bank Mega');
");
$installer->endSetup();
