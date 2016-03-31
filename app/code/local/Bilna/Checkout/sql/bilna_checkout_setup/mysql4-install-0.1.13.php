<?php
/** 
 * Add trx_from column into sales flat order 1 table.
 * The column used to determine where is order came from.
 * 
 * Option: 
 * - 1 is from logan apps
 * - 2 is from mobile apps
 * - 3 is from magento apps
 * 
 * @link http://www.sitepoint.com/magento-install-upgrade-data-scripts-explained/
 * 
 */

$installer = $this;
$installer->startSetup();

try {
    $installer->run(
        "ALTER TABLE  `".$installer->getTable('sales/order')."` 
        ADD `trx_from` INT( 2 ) NULL COMMENT 
        'Option: 1 is from logan apps, 2 is from mobile apps, 3 is from magento apps';
        
        ALTER TABLE  `".$installer->getTable('sales/quote')."` 
        ADD `trx_from` INT( 2 ) NULL COMMENT 
        'Option: 1 is from logan apps, 2 is from mobile apps, 3 is from magento apps';
        
        "
    );
}
catch (Exception $ex) {
    Mage::logException($ex);
}

$installer->endSetup();
