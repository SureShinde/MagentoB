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

class AW_Islider_Block_Adminhtml_Slider_Edit_Tabs_Images_Container extends Mage_Adminhtml_Block_Widget_Container {
    protected function _beforeToHtml() {
        if($this->getData('image_id'))
            $this->_headerText = $this->__('Edit Image');
        else
            $this->_headerText = $this->__('Add Image');
        $this->setTemplate('aw_islider/images/form_container.phtml');
    }

    public function getFormKeyHtml() {
        $_formKeyBlock = $this->getLayout()->getBlock('formkey');
        if(!$_formKeyBlock)
            $_formKeyBlock = $this->getLayout()->createBlock('core/template', 'formkey')->setTemplate('formkey.phtml');
        return $_formKeyBlock ? $_formKeyBlock->toHtml() : null;
    }

    protected function _prepareLayout() {
        $this->_addButton('save', array(
            'label'   => $this->__('Save'),
            'type' => 'submit',
            'class'   => 'save',
            'id' => 'awis_imagesavebutton'
        ));

        $this->setChild('form', $this->getLayout()->createBlock('awislider/adminhtml_slider_edit_tabs_images_ajaxform_form'));
        return parent::_prepareLayout();
    }
}
