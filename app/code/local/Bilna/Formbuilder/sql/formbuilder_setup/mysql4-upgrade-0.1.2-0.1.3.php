<?php

$installer = $this;

$installer->startSetup();

$installer->run("

ALTER TABLE bilna_formbuilder_form CHANGE static_thank static_success varchar(50) DEFAULT NULL;
ALTER TABLE bilna_formbuilder_input MODIFY form_id int(5) NOT NULL;
ALTER TABLE bilna_formbuilder_data MODIFY form_id int(5) NOT NULL;
ALTER TABLE bilna_formbuilder_data MODIFY record_id int(5) NOT NULL;
		
");

$installer->endSetup();
