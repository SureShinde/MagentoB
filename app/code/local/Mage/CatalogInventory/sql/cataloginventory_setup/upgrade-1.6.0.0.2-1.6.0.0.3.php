<?php
/** @var $installer Mage_Eav_Model_Entity_Setup */
$installer = $this;

/**
 * Add new field to 'cataloginventory/stock_item'
 * Add new field to 'sales/order'
 * Add new field to 'sales/order_item'
 * Add new field to 'sales/quote'
 * Add new field to 'sales/quote_item'
 */
$installer->getConnection()
    ->addColumn(
        $installer->getTable('cataloginventory/stock_item'),
        'max_wholesale_qty',
        array(
            'TYPE' => Varien_Db_Ddl_Table::TYPE_SMALLINT,
            'LENGTH' => 5,
            'UNSIGNED' => true,
            'NULLABLE' => false,
            'DEFAULT' => 0,
            'COMMENT' => 'Maximum quantity for wholesale feature.'
        )
    );

$installer->run("ALTER TABLE `{$this->getTable('sales_flat_order')}` ADD COLUMN `is_wholesale` smallint(5) unsigned DEFAULT 0");
$installer->run("ALTER TABLE `{$this->getTable('sales_flat_order_item')}` ADD COLUMN `is_wholesale` smallint(5) unsigned DEFAULT 0");
$installer->run("ALTER TABLE `{$this->getTable('sales_flat_quote')}` ADD COLUMN `is_wholesale` smallint(5) unsigned DEFAULT 0");
$installer->run("ALTER TABLE `{$this->getTable('sales_flat_quote_item')}` ADD COLUMN `is_wholesale` smallint(5) unsigned DEFAULT 0");
