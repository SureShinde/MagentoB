<?php

$installer = $this;
$installer->startSetup();

$table = $installer->getConnection()
	->newTable($installer->getTable('socialcommerce/collectioncategory'))
	->addColumn('category_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
		'identity'  => true,
		'nullable'  => false,
		'primary'   => true,
		), 'Category ID')
	->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
		'nullable'  => false, ), 'Cover Category Name')
		->setComment('Cover Collection Category');
$installer->getConnection()->createTable($table);

$table = $installer->getConnection()
	->newTable($installer->getTable('socialcommerce/collectioncover'))
	->addColumn('cover_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
		'identity'  => true,
		'nullable'  => false,
		'primary'   => true,
		), 'Cover ID')
	->addColumn('caption', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
		'nullable'  => false, ), 'Image Caption')
	->addColumn('image', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
		'nullable'  => false, ), 'Image File')
	->addColumn('category_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
		'nullable'  => false,
		'primary'   => true,
		), 'Category ID')
	->addIndex($installer->getIdxName('cms/page_store', array('category_id')),
		array('category_id'))
	->addForeignKey($installer->getFkName('socialcommerce/collectioncover', 'category_id', 'socialcommerce/collectioncategory', 'category_id'),
	    'category_id', $installer->getTable('socialcommerce/collectioncategory'), 'category_id',
		Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)	
	->setComment('Collection cover image');
$installer->getConnection()->createTable($table);

$installer->endSetup();
