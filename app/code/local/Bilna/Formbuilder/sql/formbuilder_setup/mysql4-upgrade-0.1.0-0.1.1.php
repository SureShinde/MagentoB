<?php
$installer = $this;
$installer->startSetup();
$installer->run("
ALTER TABLE tanyadokter ADD COLUMN submit_date datetime NOT NULL;
");
$installer->endSetup();