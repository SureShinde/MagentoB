<?php
$installer = $this;
$installer->startSetup();

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$setup->addAttribute('catalog_product', 'expected_cost', array (
    'type' => 'int',
    'label' => 'Expected Cost',
    'input' => 'text',
    'sort_order' => 200,
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'group' => 'General',
    'required' => false
));
$setup->addAttribute('catalog_product', 'event_cost', array (
    'type' => 'int',
    'label' => 'Event Cost',
    'input' => 'text',
    'sort_order' => 210,
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'group' => 'General',
    'required' => false
));
$setup->addAttribute('catalog_product', 'event_start_date', array (
    'type' => 'datetime',
    'label' => 'Event Start Date',
    'input' => 'date',
    'sort_order' => 220,
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'group' => 'General',
    'required' => false
));
$setup->addAttribute('catalog_product', 'event_end_date', array (
    'type' => 'datetime',
    'label' => 'Event End Date',
    'input' => 'date',
    'sort_order' => 230,
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'group' => 'General',
    'required' => false
));

$installer->endSetup();
