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

$installer->startSetup();

$is_old_eav = Mage::helper('brands')->compareEavVersion('1.6.0.0', '>=');

if ($is_old_eav) {
    $types = array(
            'int' => array('integer', 11),
            'text' => array('text', NULL),
            'varchar' => array('varchar', 255),
    );
} else {
    $types = array(
            'int' => 'int',
            'text' => 'text',
            'varchar' => 'varchar(255)',
    );
}

// Ceate Entity Type
$installer->addEntityType('brands', array(
        'entity_model' => 'brands/brands',
        'attribute_model' => '',
        'table' => 'brands/brands',
        'increment_model' => '',
        'increment_per_store' => '0'
));

// create eav tables
$installer->createEntityTables(
        $this->getTable('brands/brands'), array(
        'no-default-types' => TRUE,
        'types' => $types,
        )
);

// remove store_id field from entity table
$installer->getConnection()->dropColumn($installer->getTable('brands/brands'), 'store_id');

// Mage_Eav v 1.6.0.0 does not add unique index
if ($is_old_eav) {
    foreach ($types as $type => $values) {
        $eavTableName = array('brands/brands', $type);

        $installer->getConnection()->addIndex(
                $installer->getTable($eavTableName), $this->getIdxName($eavTableName, array('entity_id', 'attribute_id', 'store_id'), Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE), array('entity_id', 'attribute_id', 'store_id'), Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        );
    }
}

$installer->installEntities();
$installer->endSetup();
