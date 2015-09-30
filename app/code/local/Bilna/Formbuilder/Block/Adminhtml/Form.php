<?php
class Bilna_Formbuilder_Block_Adminhtml_Form extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_blockGroup = 'bilna_formbuilder';
	$this->_controller = 'adminhtml_formbuilder';
    //$this->_headerText = Mage::helper('bilna_formbuilder')->__('Form Builder Manager');
	$this->_headerText = $this->__('Form Builder Manager');
    parent::__construct();
	//$this->_removeButton('add'); 
  }
}
