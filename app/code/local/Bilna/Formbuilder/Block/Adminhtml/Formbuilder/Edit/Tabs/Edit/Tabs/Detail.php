<?php

class Bilna_Formbuilder_Block_Adminhtml_Formbuilder_Edit_Tabs_Edit_Tabs_Detail extends Mage_Adminhtml_Block_Widget_Form
{
   protected function _prepareForm()
   {
		$formbuilder	= Mage::registry('formbuilder_form');
    $form 				= new Varien_Data_Form();
    $form->setUseContainer(true);
		$this->setForm($form);
		$form->setHtmlIdPrefix('formbuilder_');
		$fieldset = $form->addFieldset('formbuilder_form', array('legend' => Mage::helper('bilna_formbuilder')->__('Inputs Detail')));

		$collection = Mage::getModel('bilna_formbuilder/input')->getCollection();
		$collection->addFieldToSelect('name');
		$collection->addFieldToSelect('group');
		$collection->addFieldToSelect('title');
		$collection->addFieldToSelect('type');
		//$collection->addFieldToSelect(new Zend_Db_Expr("if(required = 0, 'Enabled', 'Disabled')"), 'required');
		$collection->addFieldToSelect('required');
		//$collection->addFieldToSelect(new Zend_Db_Expr("if(`unique` = 0, 'Enabled', 'Disabled')"), 'unique');
		$collection->addFieldToSelect('unique');
		$collection->addFieldToSelect('order');
		$collection->addFieldToFilter('main_table.id', (int) $this->getRequest()->getParam('id'));

		$this->setCollection($collection);
		$data = $collection->getFirstItem();

		//$fieldset->addField('name', 'label', array(
		$fieldset->addField('name', 'text', array(
		   'label'     => 'Name',
		   'name'      => 'name',
		   'value'     => $data["name"],
    ));

		$fieldset->addField('group', 'text', array(
		   'label'     => 'Group',
		   'name'      => 'group',
		   'value'     => $data["group"],
    ));

		$fieldset->addField('title', 'text', array(
		   'label'     => 'Title',
		   'name'      => 'title',
		   'value'     => $data["title"],
    ));

		$fieldset->addField('type', 'text', array(
		   'label'     => 'Type',
		   'name'      => 'type',
		   'value'     => $data["type"],
    ));

		$fieldset->addField('required', 'select', array(
		   'label'     => 'Required',
		   'name'      => 'required',
		   //'value'   => $data["required"],
       //'note'    => Mage::helper('bilna_formbuilder')->__('Status Note:</br>0=Enabled</br>1=Disabled'),
		   'values'    => array(
             array(
                 'value' => 0,
                 'label' => Mage::helper('bilna_formbuilder')->__('Yes'),
             ),
             array(
                 'value' => 1,
                 'label' => Mage::helper('bilna_formbuilder')->__('No'),
             ),
         ),
    ));

		$fieldset->addField('unique', 'select', array(
		   'label'     => 'Unique',
		   'name'      => 'unique',
		   //'value'   => $data["unique"],
       //'note'    => Mage::helper('bilna_formbuilder')->__('Status Note:</br>0=Enabled</br>1=Disabled'),
		   'values'    => array(
             array(
                 'value' => 0,
                 'label' => Mage::helper('bilna_formbuilder')->__('Yes'),
             ),
             array(
                 'value' => 1,
                 'label' => Mage::helper('bilna_formbuilder')->__('No'),
             ),
         ),
    ));

		$fieldset->addField('order', 'text', array(
		   'label'     => 'Order',
		   'name'      => 'order',
		   'value'     => $data["order"],
    ));
		$form->setValues($data);
    return parent::_prepareForm();
	}
}
