<?php

$installer = $this;
$installer->startSetup();

// BEGIN - Create express_shipping attribute
$installer = Mage::getResourceModel('catalog/setup', 'catalog_setup');
$installer->addAttribute('catalog_product', 'express_shipping', array(
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'input' => 'boolean',
    'type' => 'int',
    'source' => 'eav/entity_attribute_source_boolean',
    'label' => 'Available for Express Shipping',
    'default' => '0',
    'required'=>'0',
    'comparable'=>'0',
    'searchable'=>'0',
    'is_configurable'=>'1',
    'user_defined'=>'1',
    'visible_on_front' => 0, //want to show on frontend?
    'visible_in_advanced_search' => 0,
    'is_html_allowed_on_front' => 1,
    'required'=> 0,
    'unique'=>false,
    'is_configurable' => false
));
$installer->endSetup();
// END - Create express_shipping attribute

// BEGIN - Assign 'express_shipping' Attribute to group 'General' for All Attribute Sets
$attSet = Mage::getModel('eav/entity_type')->getCollection()->addFieldToFilter('entity_type_code', 'catalog_product')->getFirstItem(); // This is because the you adding the attribute to catalog_products entity ( there is different entities in magento ex : catalog_category, order,invoice... etc )
$attSetCollection = Mage::getModel('eav/entity_type')->load($attSet->getId())->getAttributeSetCollection(); // this is the attribute sets associated with this entity
$attributeInfo = Mage::getResourceModel('eav/entity_attribute_collection')
    ->setCodeFilter('express_shipping')
    ->getFirstItem();
$attCode = $attributeInfo->getAttributeCode();
$attId = $attributeInfo->getId();
foreach ($attSetCollection as $a)
{
    $set = Mage::getModel('eav/entity_attribute_set')->load($a->getId());
    $setId = $set->getId();
    $group = Mage::getModel('eav/entity_attribute_group')->getCollection()
        ->addFieldToFilter('attribute_set_id', $setId)
        ->addFieldToFilter('attribute_group_name', 'General')
        ->setOrder('attribute_group_id', "ASC")->getFirstItem();
    $groupId = $group->getId();
    $newItem = Mage::getModel('eav/entity_attribute');
    $newItem->setEntityTypeId($attSet->getId()) // catalog_product eav_entity_type id ( usually 10 )
        ->setAttributeSetId($setId) // Attribute Set ID
        ->setAttributeGroupId($groupId) // Attribute Group ID ( usually general or whatever based on the query i automate to get the first attribute group in each attribute set )
        ->setAttributeId($attId) // Attribute ID that need to be added manually
        ->setSortOrder(50) // Sort Order for the attribute in the tab form edit
        ->save();
}
// END - Assign 'express_shipping' Attribute to group 'General' for All Attribute Sets