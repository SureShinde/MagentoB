<?php

class Bilna_Formbuilder_Block_Adminhtml_Formbuilder_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
	   public function __construct()
   {
        parent::__construct();
        $this->setId('formbuilder_tabs');
        //$this->setDestElementId('edit_form');
        //$this->setTitle('Form Information');
		$this->setTitle(Mage::helper('bilna_formbuilder')->__('Form Information'));
    }

   	protected function _prepareForm()
   	{
		$form = new Varien_Data_Form(array('id' => 'edit_form', 
			'action' => $this->getUrl('*/*/save', $this->formParams()),
			'method' => 'post', 
			'enctype' => 'multipart/form-data'));
		$form->setUseContainer(true);
		$this->setForm($form);
      	return parent::_prepareForm();

	}

	private function formParams()
	{
		return array (
			'form_id' => $this->getRequest()->getParam('form_id'),
			'id'	=> $this->getRequest()->getParam('id')
		);
	}
}
