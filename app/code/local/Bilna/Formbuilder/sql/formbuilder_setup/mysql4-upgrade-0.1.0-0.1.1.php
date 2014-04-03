<?php

$installer = $this;

$installer->startSetup();

$installer->run("

ALTER TABLE bilna_form_data ADD age VARCHAR(10);
ALTER TABLE bilna_form_data ADD child VARCHAR(10);

");

$installer->endSetup();
