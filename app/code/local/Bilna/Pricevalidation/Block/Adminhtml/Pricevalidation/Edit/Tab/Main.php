<?php
class Bilna_Pricevalidation_Block_Adminhtml_Pricevalidation_Edit_Tab_Main extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $source = Mage::getSingleton('bilna_pricevalidation/source');
        $profile = Mage::registry('profile_data');
        $new = !$profile || !$profile->getId();
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('profile_form', array('legend'=>$this->__('Profile Information')));
        $fieldset->addField('title', 'text', array(
            'label'     => $this->__('Title'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'title',
        ));
        $fieldset->addField('profile_status', 'select', array(
            'label'     => $this->__('Profile Status'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'profile_status',
            'values'    => $source->setPath('profile_status')->toOptionArray(),
        ));
        if ($new) {
            $fieldset->addField('profile_type', 'select', array(
                'label'     => $this->__('Profile Type'),
                'class'     => 'required-entry',
                'required'  => true,
                'name'      => 'profile_type',
                'values'    => $source->setPath('profile_type')->toOptionArray(),
            ));
            $fieldset->addField('separator', 'select', array(
                'label'     => $this->__('CSV Separator'),
                'class'     => 'required-entry',
                'required'  => true,
                'name'      => 'separator',
                'values'    => $source->setPath('separator')->toOptionArray(),
            ));
        }
        $oldWithDefaultWebsiteFlag = $source->withDefaultWebsite(!$profile || $profile->getDataType()!='sales');
        $source->withDefaultWebsite($oldWithDefaultWebsiteFlag);
        $fieldset->addField('base_dir', 'text', array(
            'label'     => $this->__('File Location'),
            'name'      => 'base_dir',
            'note'      => $this->__('Leave empty for default'),
        ));
        $fieldset->addField('filename', 'text', array(
            'label'     => $this->__('File Name'),
            'required'  => true,
            'class'     => 'required-entry',
            'name'      => 'filename',
        ));
        if (!$new) {
            $fieldset->addField('profile_type', 'select', array(
                'label'     => $this->__('Profile Type'),
                'disabled'  => true,
                'name'      => 'profile_type',
                'values'    => $source->setPath('profile_type')->toOptionArray(),
            ));
            $fieldset->addField('separator', 'select', array(
                'label'     => $this->__('CSV Separator'),
                'disabled'  => 'true',
                'name'      => 'separator',
                'values'    => $source->setPath('separator')->toOptionArray(),
            ));
            $fieldset->addField('run_status', 'select', array(
                'label'     => $this->__('Run Status'),
                'disabled'  => true,
                'name'      => 'run_status',
                'values'    => $source->setPath('run_status')->toOptionArray(),
            ));
        }
        if ($profile) {
            $form->setValues($profile->getData());
        }
        return parent::_prepareForm();
    }
}