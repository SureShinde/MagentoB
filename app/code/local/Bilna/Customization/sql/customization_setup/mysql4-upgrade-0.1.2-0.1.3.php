<?php
$installer = $this;
$installer->startSetup();

try {
    // BEGIN - Create custom_messages attribute
    $installer = Mage::getResourceModel('catalog/setup', 'catalog_setup');
    $installer->addAttribute('catalog_product', 'custom_messages', array(
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
        'input' => 'text',
        'type' => 'varchar',
        'label' => 'Custom Messages',
        'required' => 0,
        'comparable' => 0,
        'searchable' => 0,
        'is_configurable' => 0,
        'user_defined' => 1,
        'visible_on_front' => 1, //want to show on frontend?
        'visible_in_advanced_search' => 0,
        'is_html_allowed_on_front' => 1,
        'required' => 0,
        'unique' => false,
        'used_for_promo_rules' => 0,
        'used_in_product_listing' => 1
    ));
    $installer->endSetup();
    // END - Create custom_messages attribute

    // BEGIN - Assign 'custom_messages' Attribute to group 'General' for All Attribute Sets
    $attSet = Mage::getModel('eav/entity_type')->getCollection()->addFieldToFilter('entity_type_code', 'catalog_product')->getFirstItem(); // This is because the you adding the attribute to catalog_products entity ( there is different entities in magento ex : catalog_category, order,invoice... etc )
    $attSetCollection = Mage::getModel('eav/entity_type')->load($attSet->getId())->getAttributeSetCollection(); // this is the attribute sets associated with this entity
    $attributeInfo = Mage::getResourceModel('eav/entity_attribute_collection')
        ->setCodeFilter('custom_messages')
        ->getFirstItem();
    $attCode = $attributeInfo->getAttributeCode();
    $attId = $attributeInfo->getId();
    foreach ($attSetCollection as $a) {
        $set = Mage::getModel('eav/entity_attribute_set')->load($a->getId());
        $setId = $set->getId();
        $group = Mage::getModel('eav/entity_attribute_group')->getCollection()
            ->addFieldToFilter('attribute_set_id', $setId)
            ->addFieldToFilter('attribute_group_name', 'General')
            ->setOrder('attribute_group_id', "ASC")->getFirstItem();
        $groupId = $group->getId();
        $newItem = Mage::getModel('eav/entity_attribute');
        $newItem->setEntityTypeId($attSet->getId())// catalog_product eav_entity_type id ( usually 10 )
        ->setAttributeSetId($setId)// Attribute Set ID
        ->setAttributeGroupId($groupId)// Attribute Group ID ( usually general or whatever based on the query i automate to get the first attribute group in each attribute set )
        ->setAttributeId($attId)// Attribute ID that need to be added manually
        ->setSortOrder(1100)// Sort Order for the attribute in the tab form edit
        ->save();
    }
    // END - Assign 'custom_messages' Attribute to group 'General' for All Attribute Sets
} catch (Exception $e) {
    Mage::logException($e);
}