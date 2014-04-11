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
class RocketWeb_Netsuite_Block_Adminhtml_Status_Import extends Mage_Adminhtml_Block_Widget_Grid_Container {
	public function __construct()
	{
		$this->_blockGroup = 'rocketweb_netsuite';
		$this->_controller = 'adminhtml_status_import';
		$this->_headerText = Mage::helper('adminhtml')->__('Netsuite Import Status');
		parent::__construct();
		$this->_removeButton('add');
	}
}