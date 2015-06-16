<?php
$installer = $this;
$installer->startSetup();
$installer->run("
ALTER TABLE aw_collpur_deal ADD deal_image_slider text NOT NULL AFTER deal_image;
");
$installer->endSetup();