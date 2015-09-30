<?php

class Bilna_Formbuilder_Block_Adminhtml_Formbuilder_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
 public function __construct()
 {
  parent::__construct();
  $this->setId('formbuilder_form_tabs');
  $this->setDestElementId('edit_form');
	$this->setTitle(Mage::helper('bilna_formbuilder')->__('Form Information'));       
  }

  protected function _beforeToHtml()
  {
	$this->addTab('general_section', array(
    'label'		=> Mage::helper('bilna_formbuilder')->__('General'),
    'title' 	=> Mage::helper('bilna_formbuilder')->__('General'),  
		'alt' 		=> Mage::helper('bilna_formbuilder')->__('General'),  
		'url' 		=> $this->getUrl('*/*/ajaxTabGeneral', array('_current' => true)),
		'class' 	=> 'ajax',        
		'content' => $this->getLayout()->createBlock('Bilna_Formbuilder_Block_Adminhtml_Formbuilder_Edit_Tabs_General')->toHtml(),
		//'active' => Mage::registry('formbuilder_form')->getRecordId() ? false : true
  ));

	if (is_array(Mage::registry('formbuilder_form'))) {
	  $this->addTab('inputs_section', array(
	    'label'		=> Mage::helper('bilna_formbuilder')->__('Inputs'),
	    'title' 	=> Mage::helper('bilna_formbuilder')->__('Inputs'),  
			'alt' 		=> Mage::helper('bilna_formbuilder')->__('Inputs'),  
			'url' 		=> $this->getUrl('*/*/ajaxTabInputs', array('_current' => true)),
			'class' 	=> 'ajax',          
			'content' => $this->getLayout()->createBlock('Bilna_Formbuilder_Block_Adminhtml_Formbuilder_Edit_Tabs_Inputs')->toHtml()
	  ));

	  $block = $this->getLayout()->createBlock('Bilna_Formbuilder_Block_Adminhtml_Formbuilder_Edit_Tabs_Data');
		$this->addTab('data_section', array(
	    'label'		=> Mage::helper('bilna_formbuilder')->__('Data'),
	    'title' 	=> Mage::helper('bilna_formbuilder')->__('Data'),  
			'alt' 		=> Mage::helper('bilna_formbuilder')->__('Data'),
			'url' 		=> $this->getUrl('*/*/ajaxTabData', array('_current' => true)),
			'class' 	=> 'ajax',         
			'content' => ($block)? $block->toHtml(): null
	  ));
	}

	return parent::_beforeToHtml();
	}
}
