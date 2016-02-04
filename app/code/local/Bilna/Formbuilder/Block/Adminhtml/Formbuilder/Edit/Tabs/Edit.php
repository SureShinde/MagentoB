<?php

class Bilna_Formbuilder_Block_Adminhtml_Formbuilder_Edit_Tabs_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
	public function __construct()
	{
  	parent::__construct();
  	$this->_objectId 		= 'record_id';
  	$this->_blockGroup	= 'bilna_formbuilder';
  	$this->_controller	= 'adminhtml_formbuilder';
  	//$this->_removeButton('reset');
  	$this->_removeButton('addnewfield');
  	$this->_removeButton('delete');
  	//$this->_removeButton('back');
  	//$this->_mode = 'edit';
  	$this->setSaveParametersInSession(true);
  	$this->setUseAjax(true);

    $this->_addButton('delete_input', array(
      'label'   => Mage::helper('bilna_formbuilder')->__('Delete'),
      'onclick' => 'setLocation(\'' . $this->getUrl('*/*/deleteInput', array('id' => $this->getRequest()->getParam('id'))) . '\')',
      'class'   => 'delete',
    ),-1,5);

  	/*	$this->_addButton('saveandcontinue', array (
        'label' => Mage::helper('adminhtml')->__('Save And Continue Edit'),
        'onclick' => 'saveAndContinueEdit()',
        'class' => 'save',
    ), -100);*/

    $this->_updateButton('save', 'label', Mage::helper('bilna_formbuilder')->__('Save Input'));

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
