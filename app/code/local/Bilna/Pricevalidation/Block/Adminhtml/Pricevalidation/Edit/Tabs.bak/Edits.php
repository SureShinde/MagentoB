<?php

class Bilna_Formbuilder_Block_Adminhtml_Formbuilder_Edit_Tabs_Edits extends Mage_Adminhtml_Block_Widget_Form_Container
{
	public function __construct()
	{
	parent::__construct();
	$this->_objectId 		= 'record_id';
	$this->_blockGroup	= 'bilna_formbuilder';
	$this->_controller	= 'adminhtml_formbuilder';
	$this->_removeButton('reset');
	$this->_removeButton('save');
	$this->_removeButton('delete');
	//$this->_removeButton('back');
	//$this->_mode = 'edit';
	$this->setSaveParametersInSession(true);
	$this->setUseAjax(true);

  $this->_addButton('back', array(
    'label'   => Mage::helper('bilna_formbuilder')->__('Back'),
    'onclick' => 'setLocation(\'' . $this->getUrl('*/*/edit', array('id' => $this->getRequest()->getParam('form_id'))) . '\')',
    'class'   => 'back',
  ),-1,5);
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
