<?php
$installer = $this;
$installer->startSetup();
$installer->run("
	ALTER TABLE bilna_payment_confirmation ADD COLUMN entity_id INT(13) DEFAULT 0;

");
$installer->endSetup();

