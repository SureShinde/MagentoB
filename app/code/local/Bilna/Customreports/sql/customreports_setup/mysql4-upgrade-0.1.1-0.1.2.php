<?php
$installer = $this;
$installer->startSetup();
$installer->run("
    ALTER TABLE `{$installer->getTable('sales/quote_payment')}` CHANGE `anzcc_bins` `cc_bins` VARCHAR(6) DEFAULT NULL;
    ALTER TABLE `{$installer->getTable('sales/order_payment')}` CHANGE `anzcc_bins` `cc_bins` VARCHAR(6) DEFAULT NULL;

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
