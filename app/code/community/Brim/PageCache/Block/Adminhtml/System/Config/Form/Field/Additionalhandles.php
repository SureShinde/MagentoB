<?php
/**
 * Brim LLC Commercial Extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Brim LLC Commercial Extension License
 * that is bundled with this package in the file Brim-LLC-Magento-License.pdf.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.brimllc.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to support@brimllc.com so we can send you a copy immediately.
 *
 * @category   Brim
 * @package    Brim_PageCache
 * @copyright  Copyright (c) 2011-2014 Brim LLC
 * @license    http://ecommerce.brimllc.com/license
 */


class Brim_Pagecache_Block_Adminhtml_System_Config_Form_Field_Additionalhandles extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract {

    protected $_handleRenderer  = null;

    protected $_enabledRenderer = null;

    public function __construct() {

        $this->_handleRenderer = Mage::app()->getLayout()->createBlock(
            'brim_pagecache/adminhtml_system_config_form_field_handles',
            '',
            array('is_render_to_js_template' => true)
        );

        $this->addColumn('handle', array(
            'label'     => Mage::helper('adminhtml')->__('Page Type'),
            'style'     => 'width:160px',
            'renderer'  => $this->_handleRenderer,
        ));

        $this->_enabledRenderer = Mage::app()->getLayout()->createBlock(
            'brim_pagecache/adminhtml_system_config_form_field_enabled',
            '',
            array('is_render_to_js_template' => true)
        );
        $this->_enabledRenderer->addOption(1, 'Yes');
        $this->_enabledRenderer->addOption(0, 'No');

        $this->addColumn('enabled', array(
            'label'     => Mage::helper('adminhtml')->__('Enabled'),
            'style'     => 'width:40px',
            'renderer'  => $this->_enabledRenderer,
        ));
        $this->_addAfter        = false;
        $this->_addButtonLabel  = Mage::helper('adminhtml')->__('Add New Page Type');

        parent::__construct();
    }

    protected function _prepareArrayRow(Varien_Object $row) {
        $row->setData(
            'option_extra_attr_' . $this->_handleRenderer->calcOptionHash($row->getData('handle')),
            'selected="selected"'
        );
        $row->setData(
            'option_extra_attr_' . $this->_enabledRenderer->calcOptionHash($row->getData('enabled')),
            'selected="selected"'
        );
    }
}