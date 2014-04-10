<?php
$installer = $this;
$installer->startSetup();
$installer->run("
    DROP TABLE `blacklist_email`;
    ALTER TABLE `whitelist_email` ADD `type` int(1) DEFAULT 0 NOT NULL COMMENT '1->whitelist,2->graylist,3->blacklist';
");
$installer->endSetup();
	 