<?php

class Bilna_Formbuilder_Block_Adminhtml_Formbuilder_Edit_Tabs_Edit_Tab extends Mage_Adminhtml_Block_Widget_Tabs
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
    'label'		=> Mage::helper('bilna_formbuilder')->__('Data Detail'),
    'title' 	=> Mage::helper('bilna_formbuilder')->__('Data Detail'),  
		'alt' 		=> Mage::helper('bilna_formbuilder')->__('Data Detail'),  
		'url' 		=> $this->getUrl('*/*/ajaxTabDetails', array('_current' => true)),
		'class' 	=> 'ajax',        
		'content' => $this->getLayout()->createBlock('Bilna_Formbuilder_Block_Adminhtml_Formbuilder_Edit_Tabs_Edit_Tabs_Details')->toHtml(),
));

	return parent::_beforeToHtml();
	}
}
