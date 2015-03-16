<?php

class Icube_CategoryGenerator_Block_Adminhtml_Generator_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
	public function __construct()
    {
        parent::__construct();
        $this->setId('promo_catalog_form');
        $this->setTitle(Mage::helper('categorygenerator')->__('General'));
    }
    
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(
                        array(
                            'id' => 'edit_form',
                            'action' => $this->getData('action'),
                            'method' => 'post',
                            'enctype' => 'multipart/form-data',
                        )
        );
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }

}