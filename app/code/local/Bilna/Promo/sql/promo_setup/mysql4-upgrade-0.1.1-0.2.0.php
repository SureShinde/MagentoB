<?php

$installer = $this;

$installer->startSetup();

$installer->run("

DROP TABLE IF EXISTS `bilna_gimmick_event`;
CREATE  TABLE `bilna_gimmick_event` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(64) NOT NULL ,
  `banner` VARCHAR(256) NULL ,
  `callback_url` VARCHAR(64) NOT NULL DEFAULT 'http://www.bilna.com' ,
  `tos` TEXT NULL ,
  `priority` INT(2) NOT NULL DEFAULT 0 ,
  `status` INT(1) NOT NULL DEFAULT 1 ,
  `allow_repeatable` INT(1) NOT NULL DEFAULT 0 ,
  `start_date` DATE NOT NULL ,
  `end_date` DATE NOT NULL ,
  PRIMARY KEY (`id`) );


DROP TABLE IF EXISTS `bilna_gimmick_event_sku`;
CREATE  TABLE `bilna_gimmick_event_sku` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `event_id` INT NOT NULL ,
  `sku` VARCHAR(64) NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `bilna_gimmick_event_sku_idx` (`event_id` ASC) ,
  CONSTRAINT `bilna_gimmick_event_sku`
    FOREIGN KEY (`event_id` )
    REFERENCES `bilna_gimmick_event` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE);

DROP TABLE IF EXISTS `bilna_gimmick_event_applicant`;
CREATE  TABLE `bilna_gimmick_event_applicant` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `event_id` INT NOT NULL ,
  `order_increment_id` VARCHAR(50) NOT NULL ,
  `order_date` DATETIME NOT NULL ,
  `user_id` INT NOT NULL ,
  `user_email` VARCHAR(255) NOT NULL ,
  `products` VARCHAR(255) NOT NULL ,
  `status` INT(1) NOT NULL DEFAULT 0 ,
  `note` TEXT NULL ,
  `created_at` DATETIME NOT NULL ,
  `updated_at` DATETIME NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `bilna_gimmick_evet_applicant_idx` (`event_id` ASC) ,
  CONSTRAINT `bilna_gimmick_evet_applicant`
    FOREIGN KEY (`event_id` )
    REFERENCES `bilna_gimmick_event` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE);
		
");

$installer->endSetup();