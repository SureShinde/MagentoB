<?php

$installer = $this;
$installer->startSetup();
$installer->run("
    CREATE TABLE `veritrans_log_transaction` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `order_id` varchar(100) DEFAULT NULL,
        `increment_id` varchar(100) DEFAULT NULL,
        `gross_amount` bigint(20) DEFAULT NULL,
        `payment_type` varchar(100) DEFAULT NULL,
        `bank` varchar(45) DEFAULT NULL,
        `token_id` text,
        `status_code` varchar(40) DEFAULT NULL,
        `status_message` varchar(255) DEFAULT NULL,
        `transaction_id` text,
        `masked_card` text,
        `transaction_time` datetime DEFAULT NULL,
        `transaction_status` varchar(50) DEFAULT NULL,
        `fraud_status` varchar(50) DEFAULT NULL,
        `approval_code` varchar(50) DEFAULT NULL,
        `created_at` datetime DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;
");
$installer->endSetup();
