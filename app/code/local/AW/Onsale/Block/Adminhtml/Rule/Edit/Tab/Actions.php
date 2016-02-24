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
 * This package designed for Magento community edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento community edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Onsale
 * @version    2.4.0
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Onsale_Block_Adminhtml_Rule_Edit_Tab_Actions extends Mage_Adminhtml_Block_Widget_Form implements Mage_Adminhtml_Block_Widget_Tab_Interface
{

    /**
     * Prepare content for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('onsale')->__('Actions');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('onsale')->__('Actions');
    }

    /**
     * Returns status flag about this tab can be showen or not
     *
     * @return true
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return true
     */
    public function isHidden()
    {
        return false;
    }

    protected function _prepareForm()
    {
        $model = Mage::registry('current_onsale_rule');

        $form = new Varien_Data_Form();

        $form->setHtmlIdPrefix('rule_');

        $fieldset = $form->addFieldset('category_page_label', array(
            'legend' => Mage::helper('onsale')->__('Category Page Label')
                )
        );

        $fieldset->addField('category_page_show', 'select', array(
            'name' => 'category_page_show',
            'label' => Mage::helper('onsale')->__('Display'),
            'title' => Mage::helper('onsale')->__('Display'),
            'values' => array(
                0 => Mage::helper('onsale')->__('No'),
                1 => Mage::helper('onsale')->__('Yes')
            )
        ));


        $renderer = new AW_Onsale_Block_System_Config_Form_Element_Position();
        $values = Mage::getModel('onsale/system_config_source_position')->toOptionArray();

        $fieldset->addField('category_page_position', 'select', array(
            'name' => 'category_page_position',
            'label' => Mage::helper('onsale')->__('Position'),
            'title' => Mage::helper('onsale')->__('Position'),
            'values' => $values,
            'value' => ((isset($values[0]['value'])) ? ($values[0]['value']) : ''),
        ))->setRenderer($renderer);


        $fieldset->addField('category_page_image', 'image', array(
            'name' => 'category_page_image',
            'label' => Mage::helper('onsale')->__('Image'),
        ));

        $fieldset->addField('category_page_img_path', 'text', array(
            'name' => 'category_page_img_path',
            'label' => Mage::helper('onsale')->__('Image Path'),
            'after_element_html' =>
            '<p class="note"><span>' . Mage::helper('onsale')
                    ->__('/img/image.png or http://domain.com/img/image.png')
            . '</span></p>',
        ));

        $fieldset->addField('category_page_text', 'text', array(
            'name' => 'category_page_text',
            'label' => Mage::helper('onsale')->__('Text'),
            'after_element_html' => '<p class="note"><span>'
            . Mage::helper('onsale')
                    ->__('You can use predefined values in this field. Please refer to extension manual.')
            . '</span></p>',
        ));


        /* product page */


        $fieldset = $form->addFieldset('product_page_label', array(
            'legend' => Mage::helper('onsale')->__('Product Page Label')
                )
        );

        $fieldset->addField('product_page_show', 'select', array(
            'name' => 'product_page_show',
            'label' => Mage::helper('onsale')->__('Display'),
            'title' => Mage::helper('onsale')->__('Display'),
            'values' => array(
                0 => Mage::helper('onsale')->__('No'),
                1 => Mage::helper('onsale')->__('Yes')
            )
        ));


        $renderer = new AW_Onsale_Block_System_Config_Form_Element_Position();
        $values = Mage::getModel('onsale/system_config_source_position')->toOptionArray();

        $fieldset->addField('product_page_position', 'select', array(
            'name' => 'product_page_position',
            'label' => Mage::helper('onsale')->__('Position'),
            'title' => Mage::helper('onsale')->__('Position'),
            'values' => $values,
            'value' => ((isset($values[0]['value'])) ? ($values[0]['value']) : ''),
        ))->setRenderer($renderer);


        $fieldset->addField('product_page_image', 'image', array(
            'name' => 'product_page_image',
            'label' => Mage::helper('onsale')->__('Image'),
        ));

        $fieldset->addField('product_page_img_path', 'text', array(
            'name' => 'product_page_img_path',
            'label' => Mage::helper('onsale')->__('Image Path'),
            'after_element_html' => '<p class="note"><span>' . Mage::helper('onsale')
                    ->__('/img/image.png or http://domain.com/img/image.png')
            . '</span></p>',
        ));

        $fieldset->addField('product_page_text', 'text', array(
            'name' => 'product_page_text',
            'label' => Mage::helper('onsale')->__('Text'),
            'after_element_html' => '<p class="note"><span>'
            . Mage::helper('onsale')
                    ->__('You can use predefined values in this field. Please refer to extension manual.')
            . '</span></p>',
        ));


        $form->setValues($model->getData());

        //$form->setUseContainer(true);

        if ($model->isReadonly()) {
            foreach ($fieldset->getElements() as $element) {
                $element->setReadonly(true, true);
            }
        }

        $this->setForm($form);

        return parent::_prepareForm();
    }

}
