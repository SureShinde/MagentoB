<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Featured
 * @version    3.5.2
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Featured_Block_Adminhtml_Blocks_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_blocks';
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'awfeatured';

        $this->_addButton(
            'saveandcontinueedit',
            array(
                'label' => $this->__('Save And Continue Edit'),
                'onclick' => 'awafpSaveAndContinueEdit()',
                'class' => 'save',
                'id' => 'awrma-save-and-continue'
            ),
            -200
        );
        if ($this->getRequest()->getParam('id')) {
            $this->_addButton(
                'saveandduplicate',
                array(
                    'label' => $this->__('Save And Duplicate'),
                    'onclick' => 'awafpSaveAndDuplicate()',
                    'class' => 'save',
                    'id' => 'awrma-save-and-duplicate'
                ),
                -200
            );
        }
        $this->_updateButton('save', 'onclick', 'awf_submitForm()');

        $this->_formScripts[] = "awfBForm.updateAjaxUrl('"
            . Mage::helper('adminhtml')->getUrl('awfeatured/adminhtml_blocks/gettypeoptions')
            . "');
            awfBForm.updateISUrl('"
            . Mage::helper('adminhtml')->getUrl('awfeatured/adminhtml_blocks/getisform')
            . "');
            awfBForm.updateSetImageUrl('"
            . Mage::helper('adminhtml')->getUrl('awfeatured/adminhtml_blocks/setimage')
            . "');"
        ;
        $this->_formScripts[] = "
        function awf_prepareForm() {
            if($('automation_type').value == 0)
                $('quantity_limit').removeClassName('required-entry');
            else
                $('quantity_limit').addClassName('required-entry');
        }

        function awafpSaveAndContinueEdit() {
            if($('edit_form').action.indexOf('continue/1/')<0)
                $('edit_form').action += 'continue/1/';
            if($('edit_form').action.indexOf('continue_tab/')<0)
                $('edit_form').action += 'continue_tab/'+awfeatured_tabsJsTabs.activeTab.name+'/';
            awf_prepareForm();
            editForm.submit();
        }
        function awafpSaveAndDuplicate() {
            if($('edit_form').action.indexOf('duplicate/1/')<0)
                $('edit_form').action += 'duplicate/1/';
            if($('edit_form').action.indexOf('continue_tab/')<0)
                $('edit_form').action += 'continue_tab/'+awfeatured_tabsJsTabs.activeTab.name+'/';
            awf_prepareForm();
            editForm.submit();
        }
        function awf_submitForm() {
            awf_prepareForm();
            editForm.submit();
        }";
    }

    public function getHeaderText()
    {
        if (!$this->getRequest()->getParam('id')) {
            return $this->__('Add New Block');
        }
        $_block = Mage::getModel('awfeatured/blocks');
        $_block->load($this->getRequest()->getParam('id'));
        return $this->__("Edit '%s' Block", $_block->getData('block_name'));
    }
}
