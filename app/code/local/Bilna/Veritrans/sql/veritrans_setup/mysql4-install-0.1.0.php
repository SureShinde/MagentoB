<?php
$installer = $this;
$installer->startSetup();
$installer->run(
	"DROP TABLE IF EXISTS `veritrans_track`;
	CREATE TABLE `veritrans_track` (
  		`id` bigint(20) NOT NULL AUTO_INCREMENT,
  		`order_id` varchar(100) NOT NULL,
  		`session_id` varchar(150) DEFAULT NULL,
  		`gross_amount` bigint(20) NOT NULL,
		`token_browser` text,
  		`token_merchant` text,
  		`status` enum('0','1') DEFAULT '0',
  		`message` varchar(100) DEFAULT '',
  		`req_mStatus` varchar(10) NOT NULL,
  		`req_maskedCardNumber` varchar(30) NOT NULL,
  		`req_mErrMsg` varchar(50) DEFAULT '',
  		`req_vResultCode` varchar(30) DEFAULT '',
  		PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8"
);
$installer->endSetup();	
