<?php
$installer = $this;

$installer->startSetup();

$installer->run("
    ALTER TABLE  `{$this->getTable('tax_calculation_rate')}` ADD  `netsuite_internal_id` INT NOT NULL
");

$installer->run("
    ALTER TABLE  `{$this->getTable('tax_calculation_rule')}` ADD  `netsuite_internal_id` INT NOT NULL
");

$installer->endSetup();