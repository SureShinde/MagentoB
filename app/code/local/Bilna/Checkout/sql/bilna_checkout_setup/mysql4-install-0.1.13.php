<?php
/** 
 * Add trx_from column into sales flat order 1 table.
 * The column used to determine where is order came from.
 * 
 * Option: 
 * - 1 is from magento apps
 * - 2 is from logan apps
 * - 3 is from mobile apps
 * 
 * @link http://www.sitepoint.com/magento-install-upgrade-data-scripts-explained/
 * 
 */

$installer = $this;
$installer->startSetup();

try {
    $installer->run(
        "ALTER TABLE  `".$installer->getTable('sales/order')."` 
        ADD `trx_from` INT( 1 ) NULL DEFAULT  '1' COMMENT 
        'Option: 1 is from magento apps, 2 is from logan apps, 3 is from mobile apps, 4 is from affiliate';
        
        ALTER TABLE  `".$installer->getTable('sales/quote')."` 
        ADD `trx_from` INT( 1 ) NULL DEFAULT  '1' COMMENT 
        'Option: 1 is from magento apps, 2 is from logan apps, 3 is from mobile apps, 4 is from affiliate';
        
        "
    );
}
catch (Exception $ex) {
    Mage::logException($ex);
}

$installer->endSetup();
