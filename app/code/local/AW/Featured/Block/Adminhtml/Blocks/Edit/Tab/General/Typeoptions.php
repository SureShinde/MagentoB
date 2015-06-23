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


class AW_Featured_Block_Adminhtml_Blocks_Edit_Tab_General_Typeoptions extends Mage_Adminhtml_Block_Template
{
    protected function _toHtml()
    {
        $_form = new Varien_Data_Form();
        $_form->setElementRenderer(
            $this->getLayout()->createBlock('adminhtml/widget_form_renderer_element')
        );
        $_form->setFieldsetRenderer(
            $this->getLayout()->createBlock('adminhtml/widget_form_renderer_fieldset')
        );
        $_form->setFieldsetElementRenderer(
            $this->getLayout()->createBlock('adminhtml/widget_form_renderer_fieldset_element')
        );

        $_fieldset = $_form->addFieldset('types_data_fset', array('legend' => $this->__('Representation settings')));
        $_fieldset->setFieldsetContainerId('awf_types_settings');

        $type = Mage::getSingleton('awfeatured/representations_config')->getRepresentation($this->getData('type'));
        foreach ($type->getOptions() as $key => $options) {
            $_field = $_fieldset->addField(
                $key, $options['input_type'],
                array(
                'label' => $this->__($options['label']),
                'name' => 'type_data['.$key.']'
                )
            );
            if ($_field) {
                if (isset($options['default'])) {
                    $_field->setValue($options['default']);
                }
                if ($options['input_type'] == 'select' && isset($options['source_model'])) {
                    $_field->setValues(Mage::getModel($options['source_model'])->toOptionArray());
                }
                if (isset($options['required'])) {
                    $_field->setRequired($options['required']);
                }
            }
        }

        if ($this->getData('is_init')) {
            $_data = Mage::getSingleton('adminhtml/session')->getData(AW_Featured_Helper_Data::FORM_DATA_KEY);
            $_data = isset($_data['type_data']) ? $_data['type_data'] : null;
            if ($_data) {
                $_form->setValues($_data);
            }
        }
        return $_form->getHtml();
    }
}
