<?php

$installer = $this;

$installer->startSetup();

$installer->run("
CREATE TABLE IF NOT EXISTS `bilna_form` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(30) NOT NULL,
  `banner` varchar(50) DEFAULT NULL,
  `desc` text NOT NULL,
  `url` varchar(100) DEFAULT NULL,
  `status` int(1),
  PRIMARY KEY (`id`)
);

CREATE TABLE IF NOT EXISTS `bilna_form_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `form_id` int(2) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `comment` text NOT NULL,
  `submit_date` datetime NOT NULL, 
  PRIMARY KEY (`id`)
);
		
");

$installer->endSetup();