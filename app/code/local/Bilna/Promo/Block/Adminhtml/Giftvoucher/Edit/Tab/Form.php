<?php

/**
 * Unicode Systems
 * @category   Uni
 * @package    Uni_Banner
 * @copyright  Copyright (c) 2010-2011 Unicode Systems. (http://www.unicodesystems.in)
 * @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
class Bilna_Promo_Block_Adminhtml_Giftvoucher_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('banner_form', array('legend' => Mage::helper('banner')->__('Event information')));
        $version = substr(Mage::getVersion(), 0, 3);
        //$config = (($version == '1.4' || $version == '1.5') ? "'config' => Mage::getSingleton('banner/wysiwyg_config')->getConfig()" : "'class'=>''");

        $fieldset->addField('name', 'text', array(
            'label' => Mage::helper('banner')->__('Name'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'name',
        ));

        $fieldset->addField('value', 'text', array(
        		'label' => Mage::helper('banner')->__('Value'),
        		'class' => 'required-entry',
        		'required' => true,
        		'name' => 'value',
        ));

        $fieldset->addField('banner', 'image', array(
            'label' => Mage::helper('banner')->__('Banner'),
            'required' => false,
            'name' => 'banner',
        ));

        $fieldset->addField('priority', 'text', array(
        		'label' => Mage::helper('banner')->__('Priority'),
        		'class' => 'required-entry',
        		'required' => true,
        		'name' => 'priority',
        ));
        
        $fieldset->addField('start_date', 'date', array(
        		'label' => Mage::helper('banner')->__('Start Date'),
        		'title' => Mage::helper('banner')->__('Start Date'),
        		'name' => 'start_date',
        		'image' => $this->getSkinUrl('images/grid-cal.gif'),
        		'format' => "dd-MM-yyyy",
        		'value' => 'start_date',
				'required' => true,
        ));
        
        $fieldset->addField('end_date', 'date', array(
        		'label' => Mage::helper('banner')->__('End Date'),
        		'title' => Mage::helper('banner')->__('End Date'),
        		'name' => 'end_date',
        		'image' => $this->getSkinUrl('images/grid-cal.gif'),
        		'format' => "dd-MM-yyyy",
        		'value' => 'end_date',
				'required' => true,
        ));

        $fieldset->addField('status', 'select', array(
            'label' => Mage::helper('banner')->__('Status'),
            'class' => 'required-entry',
            'name' => 'status',
            'values' => array(
                array(
                    'value' => 1,
                    'label' => Mage::helper('banner')->__('Enabled'),
                ),
                array(
                    'value' => 2,
                    'label' => Mage::helper('banner')->__('Disabled'),
                ),
            ),
        ));

        if (Mage::getSingleton('adminhtml/session')->getBannerData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getBannerData());
            Mage::getSingleton('adminhtml/session')->setBannerData(null);
        } elseif (Mage::registry('giftvoucher_data')) {
            $form->setValues(Mage::registry('giftvoucher_data')->getData());
        }
        return parent::_prepareForm();
    }

}