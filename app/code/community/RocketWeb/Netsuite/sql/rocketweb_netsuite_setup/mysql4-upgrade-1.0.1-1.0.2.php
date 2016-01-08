<?php
/**
 * Rocket Web Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is available through the world-wide-web at this URL:
 * http://www.rocketweb.com/RW-LICENSE.txt
 *
 * @category   RocketWeb
 * @package    RocketWeb_Netsuite
 * @copyright  Copyright (c) 2013 RocketWeb (http://www.rocketweb.com)
 * @author     Rocket Web Inc.
 * @license    http://www.rocketweb.com/RW-LICENSE.txt
 */

$installer = $this;
$installer->startSetup();

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');

$setup->addAttribute('customer_address', 'netsuite_internal_id', array(
		'type'            => 'varchar',
		'input'            => 'text',
		'label'         => 'Netsuite internal id',
		'visible'       => true,
		'required'      => false,
		'unique'        => false,
		'position'        => 1,
));

$oAttribute = Mage::getSingleton('eav/config')->getAttribute('customer_address', 'netsuite_internal_id');
$oAttribute->setData('used_in_forms', array('adminhtml_customer_address'));
$oAttribute->save();

$installer->endSetup();