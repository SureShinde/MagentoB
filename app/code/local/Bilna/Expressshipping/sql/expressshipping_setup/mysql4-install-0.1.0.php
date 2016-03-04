<?php

$installer = $this;
$installer->startSetup();

// BEGIN - Create express_shipping attribute
$installer = Mage::getResourceModel('catalog/setup', 'catalog_setup');
$installer->addAttribute('catalog_product', 'express_shipping', array(
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'input' => 'select',
    'type' => 'int',
    'backend' => '',    // backend_model
    'frontend' => '',   // fronted_model
    'label' => 'Available for Express Shipping',
    'class' => '',
    'user_defined' => true,
    'required' => true,
    'option' => array(
        'values' => array(
            0 => 'No',
            1 => 'Yes',
        )
    ),
    'default' => 0,
    // Frontend Properties start here
    'visible' => true, // X
    'filterable' => true,    // X
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
        ->setSortOrder(200) // Sort Order for the attribute in the tab form edit
        ->save();
}
// END - Assign 'express_shipping' Attribute to group 'General' for All Attribute Sets