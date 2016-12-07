<?php
$installer = $this;
$installer->startSetup();
$installer->run("ALTER TABLE `{$this->getTable('otp_list')}` ADD COLUMN `customer_id` varchar(50) DEFAULT NULL;");
$installer->run("ALTER TABLE `{$this->getTable('otp_list')}` ADD INDEX `verify_search` (`customer_id`,`msisdn`,`otp_code`);");
$installer->endSetup();
