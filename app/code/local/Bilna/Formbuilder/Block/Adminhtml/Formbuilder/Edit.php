<?php

class Bilna_Formbuilder_Block_Adminhtml_Formbuilder_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
	public function __construct()
	{
	parent::__construct();
	$this->_objectId = 'record_id';
	$this->_blockGroup = 'bilna_formbuilder';
	$this->_controller = 'adminhtml_formbuilder';
	$this->_removeButton('reset');	//remove reset button
	$this->_removeButton('save');		//remove save button
	$this->_removeButton('delete');	
	//$this->_mode = 'edit';

	/*$this->_addButton('edit', array(
    'label'   => Mage::helper('bilna_formbuilder')->__('Edit'),
    //'onclick' => 'setLocation(\'' . $this->getUrl('*//*/edit') . '\')',
    'class'   => 'edit',
  ),0,1);*/
	}

	public function getHeaderText()
  {
    if (Mage::registry('formbuilder_formbuilder') && Mage::registry('formbuilder_formbuilder')->getRecordId())
    {
        return Mage::helper('bilna_formbuilder')->__('Formbuilder Detail "%s"', $this->htmlEscape(Mage::registry('formbuilder_formbuilder')->getName()));
    } else {
        return Mage::helper('bilna_formbuilder')->__('Formbuilder Details');
    }
  }
}
