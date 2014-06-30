<?php
/**
 * Description of mysql4-upgrade-2.0.2-2.0.3
 * 
 * Bilna Development Team <development@bilna.com>
 */

$installer = $this;
$installer->startSetup();
$installer->run("
    ALTER TABLE `{$this->getTable('sales_flat_creditmemo')}`
        ADD `netsuite_internal_id` VARCHAR(255) NOT NULL,
        ADD `last_import_date` DATETIME NOT NULL
");
$installer->endSetup();
