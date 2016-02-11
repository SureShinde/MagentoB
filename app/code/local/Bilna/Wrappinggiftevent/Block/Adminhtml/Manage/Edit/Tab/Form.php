<?php
/**
 * @package    Bilna Wrap Type Manager
 **/

class Bilna_Wrappinggiftevent_Block_Adminhtml_Manage_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {
    
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('wrappinggiftevent_form', array ('legend' => Mage::helper('wrappinggiftevent')->__('Add Wrap Type')));
        $version = substr(Mage::getVersion(), 0, 3);

        $fieldset->addField('wrapping_name', 'text', array (
            'label' => Mage::helper('wrappinggiftevent')->__('Name'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'wrapping_name'
        ));

        $fieldset->addField('wrapping_desc', 'textarea', array (
            'label' => Mage::helper('wrappinggiftevent')->__('Description'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'wrapping_desc'
        ));
		
        $fieldset->addField('wrapping_price', 'text', array (
            'label' => Mage::helper('wrappinggiftevent')->__('Price'),
            'class' => 'required-entry',
            'required' => true,
            'validate_class' => 'validate-number',
            'name' => 'wrapping_price'
        ));

        /*$fieldset->addField('wrapping_image', 'image', array (
            'label' => Mage::helper('wrappinggiftevent')->__('Wrap Image'),
            'required' => false,
            'name' => 'wrapping_image'
        ));*/
        
        $fieldset->addField('wrapping_startdate', 'date', array (
            'label' => Mage::helper('wrappinggiftevent')->__('Wrap Start Date'),
            'title' => Mage::helper('wrappinggiftevent')->__('Wrap Start Date'),
            'name' => 'wrapping_startdate',
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
            'format' => "dd-MMM-yyyy",
            'value' => 'wrapping_startdate',
            'required' => true,
        ));
        
        $fieldset->addField('wrapping_enddate', 'date', array (
            'label' => Mage::helper('wrappinggiftevent')->__('Wrap Start Date'),
            'title' => Mage::helper('wrappinggiftevent')->__('Wrap Start Date'),
            'name' => 'wrapping_enddate',
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
            'format' => "dd-MMM-yyyy",
            'value' => 'wrapping_enddate',
            'required' => true,
        ));

        if (Mage::getSingleton('adminhtml/session')->getWrappinggifteventData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getWrappinggifteventData());
            Mage::getSingleton('adminhtml/session')->setWrappinggifteventData(null);
        }
        else if (Mage::registry('wrappinggiftevent_data')) {
            $form->setValues(Mage::registry('wrappinggiftevent_data')->getData());
        }
        
        return parent::_prepareForm();
    }
}
