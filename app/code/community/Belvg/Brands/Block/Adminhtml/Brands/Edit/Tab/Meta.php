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
 * Render "Meta" tad form
 */
class Belvg_Brands_Block_Adminhtml_Brands_Edit_Tab_Meta extends Mage_Adminhtml_Block_Widget_Form implements Mage_Adminhtml_Block_Widget_Tab_Interface
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
        return Mage::helper('cms')->__('Meta Data');
    }

    /**
     * Tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('cms')->__('Meta Data');
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
        $fieldset = $form->addFieldset('brands_form_meta', array('legend' => Mage::helper('brands')->__('Meta Information')));

        $fieldset->addField('pagetitle', 'text', array(
                'label' => Mage::helper('brands')->__('Page Title'),
                'required' => FALSE,
                'name' => 'pagetitle',
                'style' => 'width:450px;',
                'maxlength' => 255,
        ));

        $fieldset->addField('pagekeyword', 'textarea', array(
                'label' => Mage::helper('brands')->__('Meta Keywords'),
                'required' => FALSE,
                'name' => 'pagekeyword',
                'style' => 'width:450px;',
        ));

        $fieldset->addField('pagedescription', 'textarea', array(
                'label' => Mage::helper('brands')->__('Meta Description'),
                'required' => FALSE,
                'name' => 'pagedescription',
                'style' => 'width:450px;',
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