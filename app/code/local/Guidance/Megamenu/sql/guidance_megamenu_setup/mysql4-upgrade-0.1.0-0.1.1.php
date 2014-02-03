<?php
/**
 * Script to create shortname attribute for category
 * @author     Guidance Magento Team <magento@guidance.com>
 * @category   Guidance
 * @package    Megamenu
 * @copyright  Copyright 2013 Guidance Solutions (http://www.guidance.com)
 */

$installer = $this;
$connection = $installer->getConnection();
$installer->startSetup();

$entityTypeId     = $installer->getEntityTypeId('catalog_category');
$attributeSetId   = $installer->getDefaultAttributeSetId($entityTypeId);
$attributeGroupId = $installer->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);

$installer->addAttribute('catalog_category', 'featuredproduct', array(
    'input'            => 'select',
    'source'           => 'guidance_megamenu/category_attribute_source_featuredproduct',
    'type'             => 'int',
    'label'            => 'Featured Product',
    'required'         => 0,
    'global'           => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
));
$installer->addAttributeToGroup($entityTypeId, $attributeSetId, $attributeGroupId, 'featuredproduct',27);

$installer->endSetup();