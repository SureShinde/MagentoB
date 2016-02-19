<?php

class Bilna_Formbuilder_Block_Adminhtml_Formbuilder_Edit_Tabs_Edit_Detail extends Mage_Adminhtml_Block_Widget_Form
{
   protected function _prepareForm()
   {
		$formbuilder = Mage::registry('formbuilder_form');

		$form = new Varien_Data_Form(array('id' => 'edit_form', 
				'action' => $this->getUrl('*/*/saveInput', array ('form_id' => $this->getRequest()->getParam('form_id'), 'id' => $this->getRequest()->getParam('id'))),
				'method' => 'post',
				'enctype' => 'multipart/form-data'));
    	$form->setUseContainer(true);
		$this->setForm($form);
		$form->setHtmlIdPrefix('formbuilder_');
		$fieldset = $form->addFieldset('formbuilder_form', array('legend' => Mage::helper('bilna_formbuilder')->__('Detail')));

		$collection = Mage::getModel('bilna_formbuilder/input')->getCollection();
		$collection->addFieldToFilter('main_table.id', (int) $this->getRequest()->getParam('id'));
		$this->setCollection($collection);

		$data = $collection->getFirstItem();

		$fieldset->addField('name', 'label', array(
		   'label'     => 'Name',
		   'name'      => 'name',
		   'value'     => $data["name"],
    ));

		$fieldset->addField('group', 'label', array(
		   'label'     => 'Group',
		   'name'      => 'group',
		   'value'     => $data["group"],
    ));

		$fieldset->addField('title', 'label', array(
		   'label'     => 'Title',
		   'name'      => 'title',
		   'value'     => $data["title"],
    ));

		$fieldset->addField('type', 'label', array(
		   'label'     => 'Type',
		   'name'      => 'type',
		   'value'     => $data["type"],
    ));

		$fieldset->addField('required', 'label', array(
		   'label'     => 'Required',
		   'name'      => 'required',
		   'value'     => $data["required"],
       'note'      => Mage::helper('bilna_formbuilder')->__('Status Note:</br>1=Enabled</br>0=Disabled'),
    ));

		$fieldset->addField('unique', 'label', array(
		   'label'     => 'Unique',
		   'name'      => 'unique',
		   'value'     => $data["unique"],
       'note'      => Mage::helper('bilna_formbuilder')->__('Status Note:</br>1=Enabled</br>0=Disabled'),
    ));

		$fieldset->addField('order', 'label', array(
		   'label'     => 'Order',
		   'name'      => 'order',
		   'value'     => $data["order"],
    ));

    return parent::_prepareForm();
	}
}
