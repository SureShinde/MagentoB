<?php

$installer = $this;
$installer->startSetup();

try {
    $installer->run("
        CREATE TABLE `log_page` (
            `id` bigint(20) NOT NULL AUTO_INCREMENT,
            `user_session` varchar(255) DEFAULT NULL,
            `product_id` varchar(255) DEFAULT NULL,
            `category_id` int(11) DEFAULT NULL,
            `page_url` varchar(255) DEFAULT NULL,
            `page_referer` varchar(255) DEFAULT NULL,
            `page_type` varchar(25) DEFAULT NULL,
            `created_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=79 DEFAULT CHARSET=latin1;
    ");
}
catch (Exception $ex) {
    Mage::logException($ex);
}

$installer->endSetup();
 