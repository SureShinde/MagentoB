<?php
$installer = $this;
$installer->startSetup();
 
$entityTypeId     = $installer->getEntityTypeId('catalog_category');
$attributeSetId   = $installer->getDefaultAttributeSetId($entityTypeId);
// $attributeGroupId = $installer->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);
$attributeGroupId=4; // To add attribute in General information tab

$installer->addAttribute('catalog_category', 'show_on_home_page',  array(
    'type'     => 'int',
    'label'    => 'Show On Home Page',
    'input'    => 'select',
    'source'   => 'eav/entity_attribute_source_boolean',
    'global'   => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'group'             => 'General',
    'visible'           => true,
    'required'          => false,
    'user_defined'      => false,
    'default'           => null
));
 
$installer->endSetup();
