<?php

$installer = $this;

$installer->startSetup();

$installer->run("

ALTER TABLE bilna_form_data ADD age VARCHAR(10);
ALTER TABLE bilna_form_data ADD child VARCHAR(10);

CREATE TABLE IF NOT EXISTS `bilna_formbuilder_form` (
  	`id` int(11) NOT NULL AUTO_INCREMENT,
  	`title` varchar(30) NOT NULL,
	`static_info` varchar(50) DEFAULT NULL,
  	`static_thank` varchar(50) DEFAULT NULL,
  	`url` varchar(100) DEFAULT NULL,
  	`active_from` datetime NOT NULL,
  	`active_to` datetime NOT NULL,
	`status` int(1),
  	PRIMARY KEY (`id`)
);

CREATE TABLE IF NOT EXISTS `bilna_formbuilder_input` (
  	`id` int(11) NOT NULL AUTO_INCREMENT,
  	`form_id` int(2) NOT NULL,
  	`name` varchar(100) NOT NULL,
  	`group` varchar(100) NOT NULL,
  	`title` varchar(100) NOT NULL,
  	`type` varchar(100) NOT NULL,
  	`required` varchar(30) NOT NULL,
  	`unique` text NOT NULL,
	`order` text NOT NULL,
  	PRIMARY KEY (`id`)
);

CREATE TABLE IF NOT EXISTS `bilna_formbuilder_data` (
  	`id` int(11) NOT NULL AUTO_INCREMENT,
  	`form_id` int(2) NOT NULL,
  	`record_id` int(2) NOT NULL,
  	`type` varchar(100) NOT NULL,
  	`value` varchar(100) DEFAULT NULL,  	
  	`create_date` datetime NOT NULL,
  	PRIMARY KEY (`id`)
);
		
");

$installer->endSetup();
