<?php
/**
 * Script to create shortname attribute for category
 * @author     Ferdian Robianto < robianto.ferdian@gmail.com >
 * @category   Icube
 * @package    Megamenu
 * @copyright  Copyright 2013 Ferdian Robianto (ferlands.com)
 */

$installer = $this;
$connection = $installer->getConnection();
$installer->startSetup();

$entityTypeId     = $installer->getEntityTypeId('catalog_category');
$attributeSetId   = $installer->getDefaultAttributeSetId($entityTypeId);
$attributeGroupId = $installer->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);

$installer->addAttribute('catalog_category', 'staticblock', array(
    'input'            => 'select',
    'source'           => 'guidance_megamenu/category_attribute_source_staticblock',
    'type'             => 'varchar',
    'label'            => 'Static Block',
    'required'         => 0,
    'global'           => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
));
$installer->addAttributeToGroup($entityTypeId, $attributeSetId, $attributeGroupId, 'staticblock',28);

$installer->endSetup();