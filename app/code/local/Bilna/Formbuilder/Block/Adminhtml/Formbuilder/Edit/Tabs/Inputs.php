<?php

class Bilna_Formbuilder_Block_Adminhtml_Formbuilder_Edit_Tabs_Inputs extends Mage_Adminhtml_Block_Widget_Form
{
   protected function _prepareForm()
   {
		$formbuilder = Mage::registry('formbuilder_form');
    $form = new Varien_Data_Form();
    $form->setUseContainer(true);
		$this->setForm($form);
		$form->setHtmlIdPrefix('formbuilder_');
		$fieldset = $form->addFieldset('formbuilder_form', array('legend' => Mage::helper('bilna_formbuilder')->__('Inputs')));

        return parent::_prepareForm();
	}
}
