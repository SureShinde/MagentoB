<?php

$installer = $this;

$installer->startSetup();

$installer->run("

ALTER TABLE bilna_price_validation_log ADD base_dir TEXT;
ALTER TABLE bilna_price_validation_log ADD source_file VARCHAR(255);
		
");

$installer->endSetup();
