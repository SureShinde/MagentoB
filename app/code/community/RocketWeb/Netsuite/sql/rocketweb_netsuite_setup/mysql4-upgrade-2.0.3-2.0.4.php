<?php
/**
 * Description of mysql4-upgrade-2.0.3-2.0.4
 * 
 * Bilna Development Team <development@bilna.com>
 */

$installer = $this;
$installer->startSetup();
$installer->run(
    "CREATE TABLE `{$this->getTable('netsuite_shipment_bundle')}` (
        `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `order_increment_id` int(11) unsigned NOT NULL,
        `sku` varchar(255) DEFAULT NULL,
        `qty_shipped` decimal(12,4) DEFAULT NULL,
        `import_date` datetime DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
);
$installer->endSetup();
