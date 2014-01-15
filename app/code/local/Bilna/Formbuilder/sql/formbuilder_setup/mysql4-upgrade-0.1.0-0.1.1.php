<?php

$installer = $this;

$installer->startSetup();

$installer->run("
INSERT INTO bilna_form_data (`id`,`name`,`email`,`phone`,`comment`,`submit_date`)
SELECT `id`,`name`,`email`,`phone`,`comment`,`submit_date` FROM `tanyadokter`;

UPDATE bilna_form_data SET form_id="1";
		
");

$installer->endSetup();