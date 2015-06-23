<?php

/**
 * BelVG LLC.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 *
  /***************************************
 *         MAGENTO EDITION USAGE NOTICE *
 * *************************************** */
/* This package designed for Magento COMMUNITY edition
 * BelVG does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * BelVG does not provide extension support in case of
 * incorrect edition usage.
  /***************************************
 *         DISCLAIMER   *
 * *************************************** */
/* Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future.
 * ****************************************************
 * @category   Belvg
 * @package    Belvg_Brands
 * @author Pavel Novitsky <pavel@belvg.com>
 * @copyright  Copyright (c) 2010 - 2012 BelVG LLC. (http://www.belvg.com)
 * @license    http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 */

/**
 * Render "General" tad form
 */
class Belvg_Brands_Block_Adminhtml_Brands_Edit_Tab_General extends Mage_Adminhtml_Block_Widget_Form implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    /**
     * IF tab is visible
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return TRUE;
    }

    /*
     * Tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('brands')->__('General');
    }

    /**
     * Tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('brands')->__('General');
    }

    /**
     * IF tab is visible
     *
     * @return boolean
     */
    public function isHidden()
    {
        return FALSE;
    }

    /**
     * Init form for rendering
     *
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();

        $this->setForm($form);
        $fieldset = $form->addFieldset('brands_form_general', array('legend' => Mage::helper('brands')->__('Brands information')));

        $fieldset->addField('title', 'text', array(
                'label' => Mage::helper('catalog')->__('Title'),
                'required' => TRUE,
                'name' => 'title',
                'style' => 'width:450px;',
                'maxlength' => 255,
        ));


        $fieldset->addField('description', 'editor', array(
                'label' => Mage::helper('catalog')->__('Description'),
                'required' => TRUE,
                'name' => 'description',
                'style' => 'width:450px;',
                'config' => Mage::getSingleton('cms/wysiwyg_config')->getConfig(),
                'wysiwyg' => TRUE
        ));

        $fieldset->addField('active', 'select', array(
                'label' => Mage::helper('catalog')->__('Status'),
                'name' => 'active',
                'required' => TRUE,
                'values' => Mage::getSingleton('adminhtml/system_config_source_enabledisable')->toOptionArray(),
        ));

        // define cstom image element
        $fieldset->addType('brands_image', 'Belvg_Brands_Model_Entity_Attribute_Image');

        $fieldset->addField('image', 'brands_image', array(
                'label' => Mage::helper('catalog')->__('Image'),
                'name' => 'image',
                'required' => FALSE,
        ));

        $fieldset->addField('url_key', 'text', array(
                'label' => Mage::helper('catalog')->__('Url Key'),
                'required' => FALSE,
                'name' => 'url_key',
                'class' => 'validate-identifier',
                'style' => 'width:450px;',
                'maxlength' => 255,
        ));

        if (Mage::getSingleton('adminhtml/session')->getBrandsData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getBrandsData());
            Mage::getSingleton('adminhtml/session')->setBrandsData(NULL);
        } elseif (Mage::registry('brands_data')) {
            $form->setValues(Mage::registry('brands_data')->getData());
        }



        return parent::_prepareForm();
    }

}