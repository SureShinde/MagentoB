<?php

require_once '../abstract.php';

/**
 * Migration Shell Script
 *
 * @author      Bilna Development Team <developmnet@bilna.com>
 */
class Moxy_Migration_Customer extends Mage_Shell_Abstract
{
	public function run()
	{
		ini_set('display_errors', 1);

		/*$resource = Mage::getSingleton('core/resource');

        $adapterReadSync  = $resource->getConnection('sync_read');
        $adapterWriteSync = $resource->getConnection('sync_write');*/

        $installer = Mage::getResourceModel('catalog/setup', 'catalog_setup');
        $installer->startSetup();

        $setup = new Mage_Eav_Model_Entity_Setup('core_setup');

		$entityTypeId     = $setup->getEntityTypeId('customer');
		$attributeSetId   = $setup->getDefaultAttributeSetId($entityTypeId);
		$attributeGroupId = $setup->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);

		$setup->addAttribute('customer', 'customer_migration', array(
				'input'         => 'select',
				'type'          => 'int',
				'label'         => 'Customer Migration',
				'visible'       => 1,
				'required'      => 0,
				'system'  		=> 1,
		));

		$setup->addAttributeToGroup(
				$entityTypeId,
				$attributeSetId,
				$attributeGroupId,
				'customer_migration',
				'999'  //sort_order
		);

		$oAttribute = Mage::getSingleton('eav/config')->getAttribute('customer', 'customer_migration');
		//$oAttribute->setData('used_in_forms', array('adminhtml_customer'));
		$oAttribute->save();

		$setup->endSetup();

	}

}


$shell = new Moxy_Migration_Customer();
$shell->run();