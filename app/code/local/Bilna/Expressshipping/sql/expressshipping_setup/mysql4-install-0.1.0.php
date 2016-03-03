<?php

$installer = $this;
$installer->startSetup();

$installer = Mage::getResourceModel('catalog/setup', 'catalog_setup');
$installer->addAttribute('catalog_product', 'express_shipping', array(
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'input' => 'boolean',
    'type' => 'int',
    'backend' => '',    // backend_model
    'frontend' => '',   // fronted_model
    'label' => 'Available for Express Shipping',
    'class' => '',
    'user_defined' => true,
    'required' => false,
    'default' => 0,

    // Frontend Properties start here
    'visible' => true, // X
    'filterable' => true,    // X
));

$installer->endSetup();