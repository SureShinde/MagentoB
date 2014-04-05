<?php

$installer = $this;

$installer->startSetup();

$installer->run("

ALTER TABLE bilna_formbuilder_input MODIFY `required` BOOLEAN;
ALTER TABLE bilna_formbuilder_input MODIFY `unique` BOOLEAN;
		
");

$installer->endSetup();
