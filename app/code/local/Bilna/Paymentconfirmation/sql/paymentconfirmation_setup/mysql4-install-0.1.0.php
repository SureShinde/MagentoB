<?php
$installer = $this;
$installer->startSetup();
$installer->run("
    DROP TABLE IF EXISTS `bilna_payment_confirmation`;
    CREATE TABLE `bilna_payment_confirmation` (
    `id` int(13) NOT NULL AUTO_INCREMENT,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `order_id` varchar(50) NOT NULL,
    `email` varchar(50) DEFAULT NULL,
    `nominal` double DEFAULT NULL,
    `dest_bank` varchar(50) NOT NULL,
    `transfer_date` date DEFAULT NULL,
    `source_bank` varchar(50) NOT NULL,
    `source_acc_number` varchar(50) NOT NULL,
    `source_acc_name` varchar(50) NOT NULL,
    `comment` text,
    `status` int(1) DEFAULT '0',
    PRIMARY KEY (`id`)
  ) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1
");
$installer->endSetup();

