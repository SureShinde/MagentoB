<?php
/**
 * @author Indra Halim
 * Penambahan field include_in_sellercenter di EAV untuk Seller Center
 */
$installer = $this;
$installer->startSetup();

// uncomment the line below if you already have include_in_sellercenter EAV
//$installer->removeAttribute('catalog_category', 'include_in_sellercenter');

$installer->addAttribute('catalog_category', 'include_in_sellercenter', array (
    'group' => 'General Information',
    'type' => 'int',
    'input' => 'select',
    'source'  => 'eav/entity_attribute_source_boolean',
    'label' => 'Include in Seller Center',
    'required' => true,
    'default' => 0,
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
));

// To populate the default data, go to shell/bilna and run:
// php populateSellercenterOption.php

$installer->endSetup();
