<?php

class Bilna_Formbuilder_Block_Adminhtml_Formbuilder_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Init class
     */
    public function __construct()
    {  
        parent::__construct();
     
        $this->setId('bilna_formbuilder_formbuilder_form');
        $this->setTitle($this->__('Formbuilder Information'));
    }  
     
    /**
     * Setup form fields for inserts/updates
     *
     * return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {  
        $model = Mage::registry('bilna_formbuilder');
     
        $form = new Varien_Data_Form(array(
            'form_id'	=> 'edit_form',
            'action'    => $this->getUrl('*/*/save', array('form_id' => $this->getRequest()->getParam('form_id'))),
            'method'    => 'post'
        ));
     
        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend'    => Mage::helper('bilna_formbuilder')->__('Formbuilder Information'),
            'class'     => 'fieldset-wide',
        ));
     
        if ($model->getId()) {
            $fieldset->addField('form_id', 'hidden', array(
                'name' => 'form_id',
            ));
        }  
     
        $fieldset->addField('name', 'text', array(
            'name'      => 'name',
            'label'     => Mage::helper('bilna_formbuilder')->__('Name'),
            'title'     => Mage::helper('bilna_formbuilder')->__('Name'),
            'required'  => true,
        ));
     
        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);
     
        return parent::_prepareForm();
    } 
}
