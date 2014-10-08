<?php

$installer = $this;

$installer->startSetup();

$installer->run("

ALTER TABLE bilna_formbuilder_data MODIFY value text NULL;

");

$installer->endSetup();
