<?php

class Bilna_Formbuilder_Block_Adminhtml_Formbuilder_Edit_Tabs_Form extends Mage_Adminhtml_Block_Widget_Form
{
   protected function _prepareForm()
   {
		$formbuilder = Mage::registry('formbuilder_form');
    $form = new Varien_Data_Form();
    $form->setUseContainer(true);
		$this->setForm($form);
		$form->setHtmlIdPrefix('formbuilder_');
		$fieldset = $form->addFieldset('formbuilder_form', array('legend' => Mage::helper('bilna_formbuilder')->__('Details')));

		$collection = Mage::getModel('bilna_formbuilder/data')->getCollection();
		$collection->getSelect()->join(array('bfi' => 'bilna_formbuilder_input'), 'main_table.form_id = bfi.form_id AND main_table.`type` = bfi.`group`', array('bfi.title'));
		$collection->addFieldToFilter('main_table.form_id', (int) $this->getRequest()->getParam('form_id'));
		$collection->addFieldToFilter('main_table.record_id', (int) $this->getRequest()->getParam('record_id'));

/*SELECT `main_table`.*,  bfi.`title`
FROM `bilna_formbuilder_data` AS `main_table`
INNER JOIN bilna_formbuilder_input bfi ON `main_table`.form_id = bfi.form_id AND `main_table`.type = bfi.`group`
WHERE `main_table`.form_id = '1' AND `main_table`.record_id = '1'*/

		$this->setCollection($collection);
		$collection->printLogQuery(true);
		foreach($collection->getData() as $detail){
		Zend_Debug::dump($detail);//die;

		}

				$fieldset->addField('value', 'label', array(
             'label'     => Mage::helper('bilna_formbuilder')->__('Name'),
             'class'     => 'required-entry',
             //'required'  => true,
             'name'      => 'g-name',
             //'note'     => Mage::helper('bilna_formbuilder')->__('The name of the example.'),
        ));

				$fieldset->addField('g-email', 'label', array(
             'label'     => Mage::helper('bilna_formbuilder')->__('Email'),
             'class'     => 'required-entry',
             //'required'  => true,
             'name'      => 'g-email',
        ));


				$fieldset->addField('g-phone', 'label', array(
             'label'     => Mage::helper('bilna_formbuilder')->__('Phone'),
             'class'     => 'required-entry',
             //'required'  => true,
             'name'      => 'g-phone',
        ));
 
        return parent::_prepareForm();
	}
}
