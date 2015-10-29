<?php

$installer = $this;
$installer->startSetup();

try {
    $installer->run("
        CREATE TABLE `api_product_flat_1` (
            `entity_id` INT(10) UNSIGNED NOT NULL COMMENT 'Entity ID',
            `detailed_info` TEXT NULL COMMENT 'Detailed Info (json)',
            `group_price` TEXT NULL COMMENT 'Group Price (json)',
            `tier_price` TEXT NULL COMMENT 'Tier Price (json)',
            `attribute_config` TEXT NULL COMMENT 'Attribute Config (json)',
            `attribute_bundle` TEXT NULL COMMENT 'Attribute Bundle (json)',
            `review` TEXT NULL COMMENT 'Review (json)',
            `images` TEXT NULL COMMENT 'Images (json)',
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`entity_id`)
        )
        COMMENT='API Catalog Product Flat (Store 1) for Solr'
        ENGINE=InnoDB;
    ");
}
catch (Exception $ex) {
    Mage::logException($ex);
}

$installer->endSetup();
