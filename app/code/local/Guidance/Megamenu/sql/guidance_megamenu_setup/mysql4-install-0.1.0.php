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

$installer->addAttribute('catalog_category', 'shortname', array(
    'input' => 'text',
    'type'  => 'varchar',
    'label' => 'Short Name',
    'backend'   => '',
    'frontend'  => '',
    'visible'   => 1,
    'required'  => 0,
    'user_defined'  => 1,
    'searchable'    => 1,
    'filterable'    => 0,
    'comparable'    => 0,
    'visible_on_front' => 1,
    'visible_in_advanced_search'    => 0,
    'global'    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
));
$installer->addAttributeToGroup($entityTypeId, $attributeSetId, $attributeGroupId, 'shortname',26);

$installer->endSetup();