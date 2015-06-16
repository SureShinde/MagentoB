<?php

$installer = $this;

$installer->startSetup();

$installer->run("

DROP TABLE IF EXISTS `bilna_promo_giftvoucher`;
CREATE  TABLE `bilna_promo_giftvoucher` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(64) NOT NULL ,
  `value` INT NOT NULL DEFAULT 0 ,
  `banner` VARCHAR(256) NULL ,
  `priority` INT(2) NOT NULL DEFAULT 0 ,
  `status` INT(1) NOT NULL DEFAULT 1 ,
  `start_date` DATE NOT NULL ,
  `end_date` DATE NOT NULL ,
  PRIMARY KEY (`id`) );
  
DROP TABLE IF EXISTS `bilna_promo_giftvoucher_member`;
CREATE  TABLE `bilna_promo_giftvoucher_member` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `promo_id` INT NOT NULL ,
  `name` VARCHAR(64) NOT NULL ,
  `email` VARCHAR(64) NOT NULL ,
  `address` VARCHAR(128) NOT NULL ,
  `order_id` VARCHAR(64) NOT NULL ,
  `submit_date` DATE NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `bilna_promo_giftvoucher_member_promo_idx` (`promo_id` ASC) ,
  CONSTRAINT `bilna_promo_giftvoucher_member_promo`
    FOREIGN KEY (`promo_id` )
    REFERENCES `bilna_promo_giftvoucher` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE);

");

$installer->endSetup();