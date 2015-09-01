<?php

$installer = $this;

$installer->startSetup();

$installer->run("
CREATE TABLE IF NOT EXISTS `bilna_price_validation_profile` (
  `profile_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `profile_type` varchar(20) NOT NULL DEFAULT 'import',
  `profile_status` varchar(20) NOT NULL DEFAULT 'disabled',
  `media_type` varchar(20) NOT NULL DEFAULT 'csv',
  `run_status` varchar(20) NOT NULL DEFAULT 'idle',
  `data_type` varchar(255) NOT NULL,
  `base_dir` text NOT NULL,
  `filename` varchar(255) NOT NULL,
  `store_id` smallint(5) unsigned NOT NULL,
  `started_at` datetime DEFAULT NULL,
  `finished_at` datetime DEFAULT NULL,
  `time_elapsed` int(10) unsigned NOT NULL,
  `last_user_id` mediumint(9) unsigned DEFAULT NULL,
  `columns_json` text,
  `profile_state_json` text,
  `memory_usage` int(10) unsigned DEFAULT NULL,
  `memory_peak_usage` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`profile_id`)
);

CREATE TABLE IF NOT EXISTS `bilna_price_validation_log` (
  `log_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `profile_id` int(10) unsigned NOT NULL,
  `started_at` datetime DEFAULT NULL,
  `finished_at` datetime DEFAULT NULL,
  `rows_found` int(10) unsigned NOT NULL,
  `rows_errors` int(10) unsigned NOT NULL,
  `user_id` mediumint(9) unsigned DEFAULT NULL,
  `error_file` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`log_id`),
  KEY `IDX_BILNA_PRICE_VALIDATION_LOG_PROFILE_ID` (`profile_id`),
  CONSTRAINT `FK_BPV_LOG_PROFILE_ID_BILNA_PRICE_VALIDATION_PROFILE_PROFILE_ID` FOREIGN KEY (`profile_id`) REFERENCES `bilna_price_validation_profile` (`profile_id`) ON DELETE CASCADE ON UPDATE CASCADE
);
		
");

$installer->endSetup();
