<?php
$installer = $this;

$installer->startSetup();

$installer->run("ALTER TABLE  `{$installer->getTable('tax_calculation_rate')}` ADD  `tax_city` VARCHAR( 255 ) NOT NULL AFTER  `tax_postcode` ,
ADD INDEX (  `tax_city` )");

$installer->endSetup();