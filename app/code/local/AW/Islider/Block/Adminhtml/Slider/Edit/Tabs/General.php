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

class AW_Islider_Block_Adminhtml_Slider_Edit_Tabs_General extends Mage_Adminhtml_Block_Widget_Form {
    protected function _prepareForm() {
        $_form = new Varien_Data_Form();
        $this->setForm($_form);
        $_data = Mage::helper('awislider')->getFormData($this->getRequest()->getParam('id'));
        if(!is_object($_data))
            $_data = new Varien_Object($_data);
        
        $_fieldset = $_form->addFieldset('general_fieldset', array(
            'legend' => $this->__('General')
        ));
        
        $_fieldset->addField('name', 'text', array(
            'name' => 'name',
            'label' => $this->__('Name'),
            'required' => TRUE
        ));
        
        $_fieldset->addField('block_id', 'text', array(
            'name' => 'block_id',
            'label' => $this->__('Block ID'),
            'required' => TRUE
        ));
        
        if(is_null($_data->getData('is_active')))
            $_data->setData('is_active', TRUE);

        $_fieldset->addField('is_active', 'select', array(
            'name' => 'block_is_active',
            'label' => $this->__('Status'),
            'required' => TRUE,
            'values' => Mage::getModel('awislider/source_status')->toOptionArray()
        ));
        
        $_fieldset->addField('autoposition', 'select', array(
            'name' => 'autoposition',
            'label' => $this->__('Automatic layout position'),
            'values' => Mage::getModel('awislider/source_autoposition')->toOptionArray()
        ));
        
        if (!Mage::app()->isSingleStoreMode()) {
            $_fieldset->addField('store', 'multiselect', array(
                'name'      => 'store[]',
                'label'     => $this->__('Store View'),
                'required'  => TRUE,
                'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(FALSE, TRUE),
            ));
        } else {
            if($_data->getStore() && is_array($_data->getStore())) {
                $_stores = $_data->getStore();
                if (isset($_stores[0]) && $_stores[0] != '') $_stores = $_stores[0];
                else $_stores = 0;
                $_data->setStore($_stores);
            }

            $_fieldset->addField('store', 'hidden', array(
                'name'      => 'store[]'
            ));
        }

        if(!$_data->getStore()) $_data->setStore(0);


        $_fieldset = $_form->addFieldset('representation_fieldset', array(
            'legend' => $this->__('Representation')
        ));

        if($_data->getData('nav_autohide') === null)
            $_data->setData('nav_autohide', 1);

        $_fieldset->addField('nav_autohide', 'select', array(
            'name' => 'nav_autohide',
            'label' => $this->__('Auto hide navigation'),
            'values' => Mage::getModel('awislider/source_navigation')->toOptionArray()
        ));
        
        $_fieldset->addField('switch_effect', 'select', array(
            'name' => 'switch_effect',
            'label' => $this->__('Switch effect'),
            'values' => Mage::getSingleton('awislider/source_switcheffects')->toOptionArray()
        ));

        if(!$_data->getData('width'))
            $_data->setData('width', '');
        
        $_fieldset->addField('width', 'text', array(
            'name' => 'width',
            'label' => $this->__('Width, px')
        ));

        if(!$_data->getData('height'))
            $_data->setData('height', '');
        
        $_fieldset->addField('height', 'text', array(
            'name' => 'height',
            'label' => $this->__('Height, px')
        ));
        
        if($_data->getData('animation_speed') === null)
            $_data->setData('animation_speed', 10);
        
        $_fieldset->addField('animation_speed', 'text', array(
            'name' => 'animation_speed',
            'label' => $this->__('Animation speed, seconds'),
            'required' => true,
            'note' => $this->__('0 disables animation')
        ));
        
        if($_data->getData('first_timeout') === null)
            $_data->setData('first_timeout', 0);
        
        $_fieldset->addField('first_timeout', 'text', array(
            'name' => 'first_timeout',
            'label' => $this->__('First frame timeout, seconds'),
            'required' => true,
            'note' => $this->__('0 means same as others')
        ));
        
        $_form->setValues($_data);
    }
}
