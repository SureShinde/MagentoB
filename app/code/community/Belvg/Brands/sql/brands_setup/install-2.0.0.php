<?php

/**
 * BelVG LLC.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 *
  /***************************************
 *         MAGENTO EDITION USAGE NOTICE *
 * *************************************** */
/* This package designed for Magento COMMUNITY edition
 * BelVG does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * BelVG does not provide extension support in case of
 * incorrect edition usage.
  /***************************************
 *         DISCLAIMER   *
 * *************************************** */
/* Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future.
 * ****************************************************
 * @category   Belvg
 * @package    Belvg_Brands
 * @author Pavel Novitsky <pavel@belvg.com>
 * @copyright  Copyright (c) 2010 - 2012 BelVG LLC. (http://www.belvg.com)
 * @license    http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 */

$installer = $this;
/* @var $installer Belvg_Brands_Model_Resource_Setup */

/**
 * Ceate Entity Type
 */
$installer->addEntityType('brands', array(
        'entity_model' => 'brands/brands',
        'attribute_model' => '',
        'table' => 'brands/brands',
        'increment_model' => '',
        'increment_per_store' => '0'
));

$baseTableName = $this->getTable('brands/brands');
$tables = array();

/**
 * Create table main eav table
 */
$connection = $this->getConnection();
$mainTable = $connection
        ->newTable($baseTableName)
        ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, NULL, array(
                'identity' => TRUE,
                'nullable' => FALSE,
                'primary' => TRUE,
                ), 'Entity Id')
        ->addColumn('entity_type_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, NULL, array(
                'unsigned' => TRUE,
                'nullable' => FALSE,
                'default' => '0',
                ), 'Entity Type Id')
        ->addColumn('attribute_set_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, NULL, array(
                'unsigned' => TRUE,
                'nullable' => FALSE,
                'default' => '0',
                ), 'Attribute Set Id')
        ->addColumn('increment_id', Varien_Db_Ddl_Table::TYPE_TEXT, 50, array(
                'nullable' => FALSE,
                'default' => '',
                ), 'Increment Id')
        ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, NULL, array(
                ), 'Created At')
        ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, NULL, array(
                ), 'Updated At')

        ->addColumn('is_active', Varien_Db_Ddl_Table::TYPE_SMALLINT, NULL, array(
                'unsigned' => TRUE,
                'nullable' => FALSE,
                'default' => '1',
                ), 'Defines Is Entity Active')
        ->addIndex($this->getIdxName($baseTableName, array('entity_type_id')), array('entity_type_id'))
        ->setComment('Brands Eav Entity Main Table');

$tables[$baseTableName] = $mainTable;

$types = array(
        'text' => array(Varien_Db_Ddl_Table::TYPE_TEXT, '64k'),
        'varchar' => array(Varien_Db_Ddl_Table::TYPE_TEXT, '255'),
        'boolean' => array(Varien_Db_Ddl_Table::TYPE_BOOLEAN, NULL),
);

/**
 * Create table array($baseTableName, $type)
 */
foreach ($types as $type => $fieldType) {
    $eavTableName = array('brands/brands', $type);

    $eavTable = $connection->newTable($this->getTable($eavTableName));
    $eavTable
            ->addColumn('value_id', Varien_Db_Ddl_Table::TYPE_INTEGER, NULL, array(
                    'identity' => TRUE,
                    'nullable' => FALSE,
                    'primary' => TRUE,
                    ), 'Value Id')
            ->addColumn('entity_type_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, NULL, array(
                    'unsigned' => TRUE,
                    'nullable' => FALSE,
                    'default' => '0',
                    ), 'Entity Type Id')
            ->addColumn('attribute_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, NULL, array(
                    'unsigned' => TRUE,
                    'nullable' => FALSE,
                    'default' => '0',
                    ), 'Attribute Id')
            ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, NULL, array(
                    'unsigned' => TRUE,
                    'nullable' => FALSE,
                    ), 'Store Id')
            ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, NULL, array(
                    'unsigned' => TRUE,
                    'nullable' => FALSE,
                    'default' => '0',
                    ), 'Entity Id')
            ->addColumn('value', $fieldType[0], $fieldType[1], array(
                    'nullable' => FALSE,
                    ), 'Attribute Value')
            ->addIndex($this->getIdxName($eavTableName, array('entity_id', 'attribute_id', 'store_id'), Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE), array('entity_id', 'attribute_id', 'store_id'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
            ->addIndex($this->getIdxName($eavTableName, array('entity_type_id')), array('entity_type_id'))
            ->addIndex($this->getIdxName($eavTableName, array('attribute_id')), array('attribute_id'))
            ->addIndex($this->getIdxName($eavTableName, array('store_id')), array('store_id'))
            ->addIndex($this->getIdxName($eavTableName, array('entity_id')), array('entity_id'));
    if ($type !== 'text') {
        $eavTable->addIndex($this->getIdxName($eavTableName, array('attribute_id', 'value')), array('attribute_id', 'value'));
        $eavTable->addIndex($this->getIdxName($eavTableName, array('entity_type_id', 'value')), array('entity_type_id', 'value'));
    }

    $eavTable
            ->addForeignKey($this->getFkName($eavTableName, 'entity_id', $baseTableName, 'entity_id'), 'entity_id', $baseTableName, 'entity_id', Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
            ->addForeignKey($this->getFkName($eavTableName, 'store_id', 'core/store', 'store_id'), 'store_id', $this->getTable('core/store'), 'store_id', Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
            ->setComment('Brands Eav Entity Value Table - ' . $type);

    $tables[$this->getTable($eavTableName)] = $eavTable;
}

foreach ($tables as $tableName => $table) {
    $connection->createTable($table);
}

$installer->installEntities();