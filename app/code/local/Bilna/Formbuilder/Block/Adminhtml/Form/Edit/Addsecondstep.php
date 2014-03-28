<?php

class Bilna_Formbuilder_Block_Adminhtml_Form_Edit_Addsecondstep extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _toHtml()
    {
        $this->setTemplate("bilna_template/formbuilder/form/default.phtml");
        return parent::_toHtml();
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();

        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('collpur')->__('General Information')));

        $fieldset->addField('product_visibility', 'select', array(
            'name' => 'product_visibility',
            'label' => Mage::helper('bilna_formbuilder')->__('Name'),
            'title' => Mage::helper('bilna_formbuilder')->__('Linked Product Visibility'),
            'options'   => Mage_Catalog_Model_Product_Visibility::getOptionArray(),
        ));

        $fieldset->addField('product_id', 'hidden', array(
            'name' => 'product_id',
            'value' => $this->getProductId()
        ));

        $fieldset->addField('form_key', 'hidden', array(
            'name' => 'form_key',
            'value' => $this->getFormKey()
        ));

        $this->setForm($form);
        return parent::_prepareForm();
    }

    public function getProductId()
    {
        return Mage::app()->getRequest()->getParam('product_id');
    }


}

