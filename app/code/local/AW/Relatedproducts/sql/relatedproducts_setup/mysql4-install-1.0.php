<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Relatedproducts
 * @version    1.4.1
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


$installer = $this;

/* $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$installer->run("

DROP TABLE IF EXISTS {$this->getTable('relatedproducts/relatedproducts')};
CREATE TABLE {$this->getTable('relatedproducts/relatedproducts')} (
  `entity_id` int(11) unsigned NOT NULL auto_increment,
  `product_id` int(10) unsigned NOT NULL,
  `related_array` text NOT NULL,
  PRIMARY KEY  (`entity_id`)
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

//      Deprecate this part. No more one time analise
//============================================================
//$orders = Mage::getModel('sales/order')->getCollection()
//	->addAttributeToSelect('*')
//	->addAttributeToFilter('status', 'complete')
//	->load();
//
//$ids = array();
//foreach($orders as $order){
//	$order = Mage::getModel('sales/order')->load($order->getId());
//	$items = $order->getAllItems();
//	if(count($items) > 1){
//		$ids = array();
//		foreach ($items as $itemId => $item){
//		   $a = $item->toArray();
//		   array_push($ids, $a['product_id']);
//		}
//	}
//
//	Mage::helper('relatedproducts')->updateRelations($ids);
//}


$installer->endSetup();