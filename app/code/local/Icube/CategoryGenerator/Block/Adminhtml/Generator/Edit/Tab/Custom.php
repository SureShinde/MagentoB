<?php

class Icube_CategoryGenerator_Block_Adminhtml_Generator_Edit_Tab_Custom extends Mage_Adminhtml_Block_Widget_Form implements Mage_Adminhtml_Block_Widget_Tab_Interface
{

	/**
	 * Prepare content for tab
	 *
	 * @return string
	 */
	public function getTabLabel()
	{
		return Mage::helper('categorygenerator')->__('Custom Conditions');
	}
	
	/**
	 * Prepare title for tab
	 *
	 * @return string
	 */
	public function getTabTitle()
	{
		return Mage::helper('categorygenerator')->__('Custom Conditions');
	}
	
	/**
	 * Returns status flag about this tab can be showed or not
	 *
	 * @return true
	 */
	public function canShowTab()
	{
		return true;
	}
	
	/**
	 * Returns status flag about this tab hidden or not
	 *
	 * @return true
	 */
	public function isHidden()
	{
		return false;
	}
	
	protected function _prepareForm()
	{
		$model = Mage::registry('cat_generator_data');
	
		$form = new Varien_Data_Form();
	
		$form->setHtmlIdPrefix('generator_');
	
		$fieldset = $form->addFieldset('base_fieldset', array('legend ' => Mage::helper('categorygenerator')->__('Custom Conditions'))
		);
	
		$fieldset->addField('is_new', 'select', array(
				'label' => Mage::helper('categorygenerator')->__('Is New?'),
				'title' => Mage::helper('categorygenerator')->__('Is New?'),
				'name' => 'is_new',
				'required' => true,
				'options' => array(
						'1' => Mage::helper('categorygenerator')->__('Yes'),
						'2' => Mage::helper('categorygenerator')->__('No'),
						'0' => Mage::helper('categorygenerator')->__('Any'),
				),
		));
	
		$fieldset->addField('is_onsale', 'select', array(
				'label' => Mage::helper('categorygenerator')->__('Is On Sale?'),
				'title' => Mage::helper('categorygenerator')->__('Is On Sale?'),
				'name' => 'is_onsale',
				'required' => true,
				'options' => array(
						'1' => Mage::helper('categorygenerator')->__('Yes'),
						'2' => Mage::helper('categorygenerator')->__('No'),
						'0' => Mage::helper('categorygenerator')->__('Any'),
				),
		));
	
		$form->setValues($model->getData());
	
		$this->setForm($form);
	
		return parent::_prepareForm();
	}
}
