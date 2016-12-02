<?php
$installer = $this;

$installer->startSetup();


$installer->endSetup();

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$entityTypeId     = $setup->getEntityTypeId('customer');
$attributeSetId   = $setup->getDefaultAttributeSetId($entityTypeId);
$attributeGroupId = $setup->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);

$setup->addAttribute('customer', 'mobile_number', array(
        'input'         => 'text',
        'type'          => 'varchar',
        'label'         => 'Mobile Number',
        'visible'       => 1,
        'required'      => 0,
        'system'  => 1,
        'default' => ''
));
$setup->addAttributeToGroup(
        $entityTypeId,
        $attributeSetId,
        $attributeGroupId,
        'mobile_number',
        '998'  //sort_order
);
$setup->addAttribute('customer', 'verified_date', array(
        'input'         => 'text',
        'type'          => 'datetime',
        'label'         => 'Verified Date',
        'visible'       => 1,
        'required'      => 0,
        'system'  => 1,
        'default' => ''
));
$setup->addAttributeToGroup(
        $entityTypeId,
        $attributeSetId,
        $attributeGroupId,
        'verified_date',
        '999'  //sort_order
);

$oAttribute = Mage::getSingleton('eav/config')->getAttribute('customer', 'mobile_number');
$oAttribute->setData('used_in_forms', array('adminhtml_customer'));
$oAttribute->save();

$oAttribute = Mage::getSingleton('eav/config')->getAttribute('customer', 'verified_date');
$oAttribute->setData('used_in_forms', array('adminhtml_customer'));
$oAttribute->save();

$setup->endSetup();

