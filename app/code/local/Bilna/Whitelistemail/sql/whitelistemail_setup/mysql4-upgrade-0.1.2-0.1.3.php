<?php
$installer = $this;
$installer->startSetup();
$installer->run("
    ALTER TABLE `whitelist_email` MODIFY `sent` tinyint NOT NULL DEFAULT '0';
    ALTER TABLE `whitelist_email` MODIFY `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1->whitelist,2->graylist,3->blacklist';
    ALTER TABLE `whitelist_email` ADD `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1->email_prepare;2->email_sent;3->email_read';
");
$installer->endSetup();
	 