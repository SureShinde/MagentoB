<?php

class Bilna_Pricevalidation_Block_Adminhtml_Pricevalidation_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('formbuilder_tabs');
        $this->setTitle(Mage::helper('bilna_pricevalidation')->__('Form Information'));
    }

    protected function _prepareForm()
    {
      $form = new Varien_Data_Form(array(
          'id' => 'edit_form',
          'action' => $this->getUrl('*/*/save', array('profile_id' => $this->getRequest()->getParam('profile_id'))),
          'method' => 'post',
          'enctype' => 'multipart/form-data'
       ));

      $form->setUseContainer(true);
      $this->setForm($form);
      return parent::_prepareForm();
    }
}
