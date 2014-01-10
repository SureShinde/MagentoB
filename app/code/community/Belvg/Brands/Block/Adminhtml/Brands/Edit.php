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
 * Build brand edit form
 */
class Belvg_Brands_Block_Adminhtml_Brands_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{

    /**
     * Init block
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'brands';
        $this->_controller = 'adminhtml_brands';

        $this->_updateButton('delete', 'label', Mage::helper('brands')->__('Delete Item'));
        $this->_updateButton('save', 'label', Mage::helper('brands')->__('Save Brand'));
    }

    /**
     * Define title
     *
     * @return string
     */
    public function getHeaderText()
    {
        $title = '';
        $_helper = Mage::helper('brands');
        if (Mage::registry('brands_data') && Mage::registry('brands_data')->getId()) {
            $title = $_helper->__("Edit Brand '%s'", $this->htmlEscape(Mage::registry('brands_data')->getTitle()));
        } else {
            $title = $_helper->__('Add Brand');
        }

        return $title;
    }

    /**
     * Prepare layout.
     * Adding save_and_continue button
     *
     * @return Mage_Widget_Block_Adminhtml_Widget_Instance_Edit
     */
    protected function _preparelayout()
    {
        $this->_addButton(
                'save_and_edit_button', array(
                'label' => Mage::helper('catalog')->__('Save and Continue Edit'),
                'class' => 'save',
                'onclick' => 'saveAndContinueEdit(\'' . $this->getSaveAndContinueUrl() . '\')'
                ), 100
        );

        return parent::_prepareLayout();
    }

    /**
     * Prepare url with back part
     *
     * @return string
     */
    public function getSaveAndContinueUrl()
    {
        return $this->getUrl('*/*/save', array(
                        '_current' => TRUE,
                        'back' => 'edit',
                        'tab' => '{{tab_id}}',
                        'active_tab' => NULL
                ));
    }

}