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


class AW_Featured_Block_Adminhtml_Blocks_Edit_Tab_Automation extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $_form = new Varien_Data_Form();
        $this->setForm($_form);
        $_data = Mage::getSingleton('adminhtml/session')->getData(AW_Featured_Helper_Data::FORM_DATA_KEY);
        if (!is_object($_data)) {
            $_data = new Varien_Object($_data);
        }

        $_dataF = array('automation_type' => $_data->getAutomationType());
        if ($_data->getAutomationData()) {
            foreach ($_data->getAutomationData() as $key => $value) {
                if ($key == 'products') {
                    $_dataF['automation_data_products'] = $value;
                }
                $_dataF[$key] = $value;
            }
        }

        $_fieldset = $_form->addFieldset('automation_settings', array('legend' => $this->__('Automation Settings')));
        $_fieldset->addField(
            'automation_type', 'select',
            array(
                'name' => 'automation_type',
                'label' => $this->__('Automation Type'),
                'values' => Mage::getModel('awfeatured/source_automation')->toOptionArray()
            )
        );

        $_fieldset->addField(
            'product_sorting_type', 'select',
            array(
                'name' => 'product_sorting_type',
                'label' => $this->__('Product Sorting Type'),
                'values' => Mage::getModel('awfeatured/source_automation_productsort')->toOptionArray()
            )
        );

        $automationDataProducts = array();
        if (array_key_exists('automation_data_products', $_dataF) && is_array($_dataF['automation_data_products'])) {
            $automationDataProducts = $_dataF['automation_data_products'];
        }
        $productGridHtml = Mage::getSingleton('core/layout')
            ->createBlock(
                'awfeatured/adminhtml_blocks_edit_tab_automation_productsgrid', null,
                array('automation_data_products' => $automationDataProducts)
            )
            ->toHtml()
        ;
        $_fieldset->addField(
            'gridcontainer_products', 'note',
            array(
                'label' => $this->__('Select Products'),
                'text' => $productGridHtml
            )
        );

        $_fieldset->addField('automation_data_products', 'hidden', array('name' => 'automation_data[products]'));

        $categoryGridHtml = Mage::getSingleton('core/layout')
            ->createBlock('awfeatured/adminhtml_blocks_edit_tab_automation_categoriesgrid')
            ->toHtml()
        ;
        $_fieldset->addField(
            'gridcontainer_categories', 'note',
            array(
                'label' => $this->__('Select Categories'),
                'text' => $categoryGridHtml
            )
        );

        $_fieldset->addField('automation_data_categories', 'hidden', array('name' => 'automation_data[categories]'));
        $_fieldset->addField(
            'current_category_type', 'select',
            array(
                'name' => 'current_category_type',
                'label' => $this->__('Current Category Automation Type'),
                'values' => Mage::getModel('awfeatured/source_automation_currentcategory')->toOptionArray()
            )
        );

        $_fieldset->addField(
            'quantity_limit', 'text',
            array(
                'name' => 'automation_data[quantity_limit]',
                'label' => $this->__('Quantity Limit'),
                'required' => true
            )
        );
        $_form->setValues($_dataF);
    }
}
