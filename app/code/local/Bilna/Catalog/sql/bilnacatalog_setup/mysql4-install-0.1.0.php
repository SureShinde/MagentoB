<?php
$installer = $this;
$installer->startSetup();
$installer->addAttribute('catalog_category', 'complete_url', array (
    'group' => 'General Information',
    'type' => 'varchar',
    'input' => 'text',
    'label' => 'Complete URL',
    'required' => false,
    'visible' => false,
    'visible_on_front' => false,
    'filterable' => false,
    'user_defined' => true,
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE
));
$installer->endSetup();
