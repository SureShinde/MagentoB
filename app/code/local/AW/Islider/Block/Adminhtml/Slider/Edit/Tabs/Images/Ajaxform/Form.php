<?php
/**
* aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Islider
 * @version    1.3.1
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 */

class AW_Islider_Block_Adminhtml_Slider_Edit_Tabs_Images_Ajaxform_Form extends Mage_Adminhtml_Block_Widget_Form {
    protected function _prepareForm() {
        $_form = new Varien_Data_Form(array(
            'id' => 'edit_image_form',
            'method' => 'post'
        ));
        $this->setForm($_form);

        $_formData = Mage::helper('awislider')->getFormDataImage($this->getRequest()->getParam('id'));
        if(is_object($_formData) && $_formData->getData()) {
            $_formData->setData(array(
                'image_id' => $_formData->getData('id'),
                'image_pid' => $_formData->getData('pid'),
                'image_type' => $_formData->getData('type'),
                'image_remote' => $_formData->getData('type') == AW_Islider_Model_Source_Images_Type::FILE ? '' : $_formData->getData('location'),
                'image_title' => $_formData->getData('title'),
                'image_is_active' => $_formData->getData('is_active'),
                'image_from' => !$_formData->getData('active_from') || @strtotime ($_formData->getData('active_from')) < 1 ? '' : $_formData->getData('active_from'),
                'image_to' => !$_formData->getData('active_to') || @strtotime ($_formData->getData('active_to')) < 1 ? '' : $_formData->getData('active_to'),
                'sort_order' => $_formData->getData('sort_order'),
                'image_url' => $_formData->getData('url'),
                'image_in_new_window' => $_formData->getData('new_window'),
                'image_nofollow' => $_formData->getData('nofollow'),
                'location' => $_formData->getData('location')
            ));
        } else {
            $_formData = array(
                'image_pid' => $this->getRequest()->getParam('pid'),
                'image_tmp_id' => uniqid(),
                'image_is_active' => 1,
                'sort_order' => 0
            );
        }

        $_fieldset = $_form->addFieldset('general_fieldset', array(
            'legend' => $this->__('General Information')
        ));

        $_fieldset->addField('image_pid', 'hidden', array(
            'name' => 'image_pid',
            'value' => $this->getRequest()->getParam('pid')
        ));

        if($this->getRequest()->getParam('id')) {
            $_fieldset->addField('image_id', 'hidden', array(
                'name' => 'image_id'
            ));
        } else {
            $_fieldset->addField('image_tmp_id', 'hidden', array(
                'name' => 'image_tmp_id'
            ));
        }

        $_fieldset->addField('image_type', 'select', array(
            'name' => 'image_type',
            'label' => $this->__('Image Type'),
            'values' => Mage::getModel('awislider/source_images_type')->toOptionArray()
        ));

        $_fieldset->addField('image_file', 'file', array(
            'name' => 'image_file',
            'label' => $this->__('Image'),
            'required' => true,
            'note' => is_object($_formData) && $_formData->getData('image_type') == AW_Islider_Model_Source_Images_Type::FILE ? $this->__('Current file: %s', $_formData->getData('location')) : null
        ));

        $_fieldset->addField('image_remote', 'text', array(
            'name' => 'image_remote',
            'label' => $this->__('Image'),
            'required' => true
        ));

        $_fieldset->addField('image_title', 'text', array(
            'name' => 'image_title',
            'label' => $this->__('Image Title'),
            'required' => false
        ));

        $_fieldset->addField('image_is_active', 'select', array(
            'name' => 'image_is_active',
            'label' => $this->__('Status'),
            'values' => Mage::getModel('awislider/source_status')->toOptionArray()
        ));

        $dateFormatIso = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM);
        
        $_fieldset->addField('image_from', 'date', array(
            'name' => 'image_from',
            'label' => $this->__('Date From'),
            'image'  => $this->getSkinUrl('images/grid-cal.gif'),
            'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
            'format'       => $dateFormatIso,
            'locale'       => Mage::app()->getLocale()->getLocaleCode(),
            'required' => true
        ));

        $_fieldset->addField('image_to', 'date', array(
            'name' => 'image_to',
            'label' => $this->__('Date To'),
            'image'  => $this->getSkinUrl('images/grid-cal.gif'),
            'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
            'format'       => $dateFormatIso,
            'locale'       => Mage::app()->getLocale()->getLocaleCode(),
            'required' => true
        ));

        $_fieldset->addField('sort_order', 'text', array(
            'name' => 'image_sort_order',
            'label' => $this->__('Sort Order'),
            'required' => true
        ));

        $_fieldset = $_form->addFieldset('url_settings', array(
            'legend' => $this->__('URL Settings')
        ));

        $_fieldset->addField('image_url', 'text', array(
            'name' => 'image_url',
            'label' => $this->__('URL')
        ));

        $_fieldset->addField('image_in_new_window', 'select', array(
            'name' => 'image_in_new_window',
            'label' => $this->__('Open URL in new window'),
            'values' => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray()
        ));

        $_fieldset->addField('image_nofollow', 'select', array(
            'name' => 'image_nofollow',
            'label' => $this->__('Add \'No follow\' to URL'),
            'values' => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray()
        ));

        $_form->setValues($_formData);

        return parent::_prepareForm();
    }
}
