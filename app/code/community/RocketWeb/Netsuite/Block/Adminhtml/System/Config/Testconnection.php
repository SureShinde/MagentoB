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
class RocketWeb_Netsuite_Block_Adminhtml_System_Config_Testconnection extends Mage_Adminhtml_Block_System_Config_Form_Field {
	protected function _prepareLayout()
	{
		parent::_prepareLayout();
		if (!$this->getTemplate()) {
			$this->setTemplate('rocketweb_netsuite/system/config/testconnection.phtml');
		}
		return $this;
	}
	
	public function render(Varien_Data_Form_Element_Abstract $element)
	{
		$element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
		return parent::render($element);
	}
	
	protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
	{
		$originalData = $element->getOriginalData();
		$this->addData(array(
				'button_label' => Mage::helper('rocketweb_netsuite')->__($originalData['button_label']),
				'html_id' => $element->getHtmlId(),
				'ajax_url' => Mage::getSingleton('adminhtml/url')->getUrl('*/system_config_testconnection/connect')
		));
	
		return $this->_toHtml();
	}
}