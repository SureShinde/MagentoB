<?php
/**
 * @author Indra Halim
 * Penambahan field include_in_megamenu di EAV untuk Catalog Category
 */
$installer = $this;
$installer->startSetup();
$installer->addAttribute('catalog_category', 'include_in_megamenu', array (
    'group' => 'General Information',
    'type' => 'int',
    'input' => 'select',
    'source'  => 'eav/entity_attribute_source_boolean',
    'label' => 'Include in Mega Menu',
    'required' => true,
    'default' => 0,
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
));

//Populate value attribute di catalog/category
$categoryModel = Mage::getModel('catalog/category');
$catTree = $categoryModel->getTreeModel()->load();
$catIds = $catTree->setStoreId(0)->getCollection()->getAllIds();
foreach($catIds as $id){
    $categoryModel->setId($id);
    $categoryModel->setIncludeInMegamenu(1);
    $categoryModel->save();
}

$installer->endSetup();
