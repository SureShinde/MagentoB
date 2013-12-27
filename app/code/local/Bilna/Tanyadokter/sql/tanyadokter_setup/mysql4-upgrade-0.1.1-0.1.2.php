<?php
$installer = $this;
$installer->startSetup();
$installer->run("
ALTER TABLE tanyadokter CHANGE comment comment TEXT NOT NULL;
");
$installer->endSetup();