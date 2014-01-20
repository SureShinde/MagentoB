<?php

$installer = $this;

$installer->startSetup();

$installer->run("
alter table shipping_premiumrate add column shipping_type varchar (64) default null;
");

$installer->endSetup();