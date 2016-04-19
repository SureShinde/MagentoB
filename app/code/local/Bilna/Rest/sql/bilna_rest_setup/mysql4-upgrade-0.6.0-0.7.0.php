<?php

$installer = $this;
$installer->startSetup();

try {
    $installer->run(
        "CREATE TABLE `veritrans_api_log` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `order_no` varchar(50) DEFAULT NULL COMMENT 'Order Increment Id',
            `request` text NOT NULL,
            `response` text NOT NULL,
            `type` enum('C','N') NOT NULL DEFAULT 'C' COMMENT 'C=>Charge, N=>Notification',
            `created_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1;"
    );
}
catch (Exception $ex) {
    Mage::logException($ex);
}

$installer->endSetup();
