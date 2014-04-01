<?php
/**
 * Description of Bilna_Paymethod_Block_Adminhtml_Binmanage_Edit_Form
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Paymethod_Block_Adminhtml_Binmanage_Edit_Form extends Mage_Adminhtml_Block_Widget_Form {
    protected function _prepareForm() {
        if (Mage::getSingleton('adminhtml/session')->getBinmanageData()) {
            $data = Mage::getSingleton('adminhtml/session')->getBinmanageData();
            Mage::getSingleton('adminhtml/session')->getBinmanageData(null);
        }
        else if (Mage::registry('binmanage_data')) {
            $data = Mage::registry('binmanage_data')->getData();
        }
        else {
            $data = array ();
        }
 
        $form = new Varien_Data_Form(array (
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/save', array ('id' => $this->getRequest()->getParam('id'))),
            'method' => 'post',
            'enctype' => 'multipart/form-data',
        ));
 
        $form->setUseContainer(true);
        $this->setForm($form);
 
        $helper = Mage::helper('paymethod/binmanage');
        $fieldset = $form->addFieldset('binmanage_form', array (
            'legend' => $helper->__('Bin Information')
        ));
        $fieldset->addField('code', 'text', array (
            'label' => $helper->__('Bin Number'),
            'class' => 'required-entry validate-number validate-length maximum-length-6',
            'required' => true,
            'name' => 'code',
            'maxlength' => 6,
            'note' => $helper->__('Maximum length 6 digits'),
        ));
        $fieldset->addField('platform', 'select', array (
            'label' => $helper->__('Platform'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'platform',
            'values' => $this->getBinPlatformOptions(),
        ));
        $fieldset->addField('issuer', 'select', array (
            'label' => $helper->__('Payment Method'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'issuer',
            'values' => $this->getBinPaymentmethodOptions(),
        ));
        $fieldset->addField('name', 'text', array (
            'label' => $helper->__('Bank Information'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'name',
        ));
 
        $form->setValues($data);
 
        return parent::_prepareForm();
    }
    
    protected function getBinPlatformOptions() {
        $platforms = array ('Visa', 'MasterCard');
        $result = array ();
        
        foreach ($platforms as $platform) {
            $result[] = array (
                'value' => $platform,
                'label' => $platform
            );
        }
        
        return $result;
    }
    
    protected function getBinPaymentmethodOptions() {
        $paymentCollection = explode(',', Mage::getStoreConfig('bilna_customreports/installmentreport/payment_allow'));
        $result = array ();
       
        foreach ($paymentCollection as $key => $value) {
            $result[] = array (
                'value' => $value,
                'label' => $this->helper('customreports/installmentreport')->getPaymentmentOptionLabel($value)
            );
        }
        
        return $result;
    }
}
