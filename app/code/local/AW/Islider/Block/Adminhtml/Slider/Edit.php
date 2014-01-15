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

class AW_Islider_Block_Adminhtml_Slider_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {
    public function __construct() {
        $this->_controller = 'adminhtml_slider';
        parent::__construct();
        $this->_objectId = 'id';
        $this->_blockGroup = 'awislider';

        if($this->getRequest()->getParam('id') && Mage::getSingleton('admin/session')->isAllowed('cms/awislider/new')) {
            $this->_addButton('addimage', array(
                'label' => $this->__('Add Image'),
                'onclick' => 'awisAddImage()',
                'class' => 'add',
                'id' => 'awis-add-image'
            ), 0);
        }
        
        $this->_addButton('saveandcontinueedit', array(
            'label' => $this->__('Save And Continue Edit'),
            'onclick' => 'awisSaveAndContinueEdit()',
            'class' => 'save',
            'id' => 'awis-save-and-continue'
        ), -200);
        
        $this->_formScripts[] = "
        function awisAddImage() {
            awislider_tabsJsTabs.tabs[1].show();
            awISAjaxForm.showForm(".$this->getRequest()->getParam('id').");
        }
        function awis_prepareForm() {
        }
        function awisSaveAndContinueEdit() {
            if($('edit_form').action.indexOf('continue/1/')<0)
                $('edit_form').action += 'continue/1/';
            if($('edit_form').action.indexOf('continue_tab/')<0)
                $('edit_form').action += 'continue_tab/'+awislider_tabsJsTabs.activeTab.name+'/';
            awis_prepareForm();
            editForm.submit();
        }
        if(awISSettings)
            awISSettings.setOption('imagesAjaxFormUrl', '{$this->getUrl('awislider_admin/adminhtml_image/ajaxform')}');";

        if(!Mage::getSingleton('admin/session')->isAllowed('cms/awislider/new')) {
            $this->_removeButton('save');
            $this->_removeButton('saveandcontinueedit');
            $this->_removeButton('delete');
            $this->_removeButton('reset');
        }
    }
    
    public function getHeaderText() {
        return $this->__('Slider');
    }
}
