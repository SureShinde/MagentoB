<?php
$installer = $this;
$installer->startSetup();
$installer->run("
ALTER TABLE `bilna_price_validation_profile` ADD COLUMN `separator` VARCHAR(1);
		
");
$installer->endSetup();
