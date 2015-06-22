<?php
$installer = $this;
$installer->startSetup();
$installer->addAttribute('catalog_category', 'asi_disclaimer', array (
    'group' => 'General Information',
    'type' => 'int',
    'input' => 'select',
    'source'  => 'eav/entity_attribute_source_boolean',
    'label' => 'ASI Disclaimer',
    'required' => true,
    'default' => '0',
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
));     
$installer->endSetup();
