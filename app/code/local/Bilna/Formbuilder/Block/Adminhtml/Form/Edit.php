<?php

class Bilna_Formbuilder_Block_Adminhtml_Form_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
	/**
	* Constructor
	*/
	public function __construct()
	{
		parent::__construct();
		$this->_blockGroup = 'bilna_formbuilder';
		$this->_controller = 'adminhtml_form';
		$this->_headerText = Mage::helper('bilna_formbuilder')->__('Edit Form');
	}
}
