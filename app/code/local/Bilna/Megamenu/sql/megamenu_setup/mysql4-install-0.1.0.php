<?php
$installer = $this;
$installer->startSetup();
$installer->addAttribute('catalog_category', 'megamenu_static_block', array (
    'group' => 'Megamenu Feature',
    'type' => 'int',
    'input' => 'select',
    'source'  => 'megamenu/catalog_category_attribute_source_megamenu',
    'label' => 'Megamenu static block',
    'required' => false,
    'visible' => true,
    'default' => '0',
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'sort_order' => 26
));
$installer->addAttribute('catalog_category', 'megamenu_image', array (
    'group' => 'Megamenu Feature',
    'type' => 'varchar',
    'label' => 'Megamenu Image',
    'input' => 'image',
    'backend' => 'catalog/category_attribute_backend_image',
    'required' => false,
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'sort_order' => 27
));
$installer->endSetup();
