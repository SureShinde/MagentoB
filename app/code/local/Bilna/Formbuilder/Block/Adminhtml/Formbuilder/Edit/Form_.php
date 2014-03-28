<?php

class Bilna_Formbuilder_Block_Adminhtml_Formbuilder_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
		public function __construct()
    {
        parent::__construct();
        $this->setRecordId('record_id');
        $this->setTitle(Mage::helper('bilna_formbuilder')->__('Form Information'));
        
    }
    /**
     * Setup form fields for inserts/updates
     *
     * return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {       
			$form = new Varien_Data_Form(
				array(
					'record_id'	=> 'edit_form',
					//'action'    => $this->getUrl('*/*/save', array('record_id' => $this->getRequest()->getParam('record_id'))),
					'action' => $this->getData('action'),
          'method'    => 'post',
					'enctype' => 'multipart/form-data'
        ));
     /*
        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend'    => Mage::helper('bilna_formbuilder')->__('Formbuilder Information'),
            'class'     => 'fieldset-wide',
        ));
     
        $fieldset->addField('name', 'text', array(
            'name'      => 'name',
            'label'     => Mage::helper('bilna_formbuilder')->__('Name'),
            'title'     => Mage::helper('bilna_formbuilder')->__('Name'),
            'required'  => true,
        ));
     */
        //$form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);
     
        return parent::_prepareForm();
    } 
}
