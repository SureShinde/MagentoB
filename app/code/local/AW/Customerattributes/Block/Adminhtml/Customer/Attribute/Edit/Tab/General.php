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
 * @package    AW_Customerattributes
 * @version    1.0.2
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Customerattributes_Block_Adminhtml_Customer_Attribute_Edit_Tab_General
    extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    public function getTabLabel()
    {
        return $this->__('General');
    }

    public function getTabTitle()
    {
        return $this->__('General');
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }

    protected function _prepareForm()
    {
        $this->_initForm()->_setFormValues();
        return parent::_prepareForm();
    }

    protected function _initForm()
    {
        $form = new Varien_Data_Form();

        $generalFieldset = $form->addFieldset(
            'attribute_general',
            array('legend' => $this->__('Attribute Properties'))
        );

        $generalFieldset->addField(
            'is_enabled',
            'select',
            array(
                'label'    => $this->__('Enabled'),
                'name'     => 'is_enabled',
                'required' => true,
                'values'   => Mage::getModel('aw_customerattributes/source_yesno')->toOptionArray(),
            )
        );

        $generalFieldset->addField(
            'code',
            'text',
            array(
                'label'    => $this->__('Attribute Code'),
                'name'     => 'code',
                'required' => true,
                'class'    => 'validate-code',
                'disabled' => $this->getAttribute()->getId() ? true : false,
            )
        );

        $generalFieldset->addField(
            'type',
            'select',
            array(
                'label'    => $this->__('Attribute Type'),
                'name'     => 'type',
                'required' => true,
                'values'   => Mage::getModel('aw_customerattributes/source_type')->toOptionArray(),
                'disabled' => $this->getAttribute()->getId() ? true : false,
            )
        );

        $frontendPropertiesFieldset = $form->addFieldset(
            'attribute_frontend',
            array('legend' => $this->__('Frontend Properties'))
        );

        $frontendPropertiesFieldset->addField(
            'sort_order',
            'text',
            array(
                'label' => $this->__('Sort Order'),
                'name'  => 'sort_order',
                'class' => 'validate_number',
            )
        );

        if (!Mage::app()->isSingleStoreMode()) {
            $frontendPropertiesFieldset->addField(
                'store_ids',
                'multiselect',
                array(
                    'name'   => 'store_ids[]',
                    'label'  => $this->__('Store View'),
                    'values' => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, false),
                )
            );
        } else {
            $frontendPropertiesFieldset->addField(
                'store_ids',
                'hidden',
                array(
                    'name' => 'store_ids[]',
                )
            );
        }

        $frontendPropertiesFieldset->addField(
            'customer_groups',
            'multiselect',
            array(
                'name'   => 'customer_groups[]',
                'label'  => $this->__('Customer Groups'),
                'title'  => $this->__('Customer Groups'),
                'values' => Mage::getResourceModel('customer/group_collection')->load()->toOptionArray(),
            )
        );

        $frontendPropertiesFieldset->addField(
            'display_on',
            'multiselect',
            array(
                'name'   => 'display_on[]',
                'label'  => $this->__('Display On'),
                'title'  => $this->__('Display On'),
                'values' => Mage::getModel('aw_customerattributes/source_displayon')->toOptionArray(),
            )
        );

        $frontendPropertiesFieldset->addField(
            'is_editable_by_customer',
            'select',
            array(
                'label'    => $this->__('Editable by Customer'),
                'name'     => 'is_editable_by_customer',
                'required' => true,
                'values'   => Mage::getModel('aw_customerattributes/source_yesno')->toOptionArray(),
            )
        );

        $frontendPropertiesFieldset->addField(
            'is_display_in_grid',
            'select',
            array(
                'label'    => $this->__('Display in Customer Grid'),
                'name'     => 'is_display_in_grid',
                'required' => true,
                'values'   => Mage::getModel('aw_customerattributes/source_yesno')->toOptionArray(),
            )
        );

        $additionalFieldset = $form->addFieldset(
            'attribute_validation',
            array('legend' => $this->__('Additional Properties'))
        );

        $additionalFieldset->addField(
            'is_required',
            'select',
            array(
                'label'  => $this->__('Required'),
                'name'   => 'is_required',
                'values' => Mage::getModel('aw_customerattributes/source_yesno')->toOptionArray(),
            )
        );

        $additionalFieldset->addField(
            'default_value_text',
            'text',
            array(
                'label' => $this->__('Default Value'),
                'name'  => 'default_value_text',
            )
        );

        $additionalFieldset->addField(
            'default_value_textarea',
            'textarea',
            array(
                'label' => $this->__('Default Value'),
                'name'  => 'default_value_textarea',
            )
        );

        $additionalFieldset->addField(
            'default_value_date',
            'date',
            array(
                'label'  => $this->__('Default Value'),
                'name'   => 'default_value_date',
                'image'  => $this->getSkinUrl('images/grid-cal.gif'),
                'format' => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
            )
        );

        $source = Mage::getModel('aw_customerattributes/attribute_type_yesno')->getAvailableOptionArray();
        $additionalFieldset->addField(
            'default_value_yesno',
            'select',
            array(
                'label'  => $this->__('Default Value'),
                'name'   => 'default_value_yesno',
                'values' => $source,
            )
        );

        $additionalFieldset->addField(
            'minimum_text_length',
            'text',
            array(
                'label' => $this->__('Minimum Text Length'),
                'name'  => 'minimum_text_length',
                'class' => 'validate-zero-or-greater',
                'note'  => $this->__('Leave empty to disable this validation'),
            )
        );

        $additionalFieldset->addField(
            'maximum_text_length',
            'text',
            array(
                'label' => $this->__('Maximum Text Length'),
                'name'  => 'maximum_text_length',
                'class' => 'validate-zero-or-greater',
                'note'  => $this->__('Leave empty to disable this validation'),
            )
        );

        $additionalFieldset->addField(
            'input_validation',
            'select',
            array(
                'label'  => $this->__('Input Validation'),
                'name'   => 'input_validation',
                'values' => Mage::getModel('aw_customerattributes/source_validation')->toOptionArray(),
            )
        );

        $additionalFieldset->addField(
            'is_unique',
            'select',
            array(
                'label'  => $this->__('Unique Value'),
                'name'   => 'is_unique',
                'values' => Mage::getModel('aw_customerattributes/source_yesno')->toOptionArray(),
            )
        );

        $additionalFieldset->addField(
            'image_height',
            'text',
            array(
                'label' => $this->__('Recommended Image Height (pixels)'),
                'name'  => 'image_height',
                'class' => 'validate-zero-or-greater',
                'note'  => $this->__('Leave empty to disable this validation'),
            )
        );

        $additionalFieldset->addField(
            'image_width',
            'text',
            array(
                'label' => $this->__('Recommended Image Width (pixels)'),
                'name'  => 'image_width',
                'class' => 'validate-zero-or-greater',
                'note'  => $this->__('Leave empty to disable this validation'),
            )
        );

        $additionalFieldset->addField(
            'filesize',
            'text',
            array(
                'label' => $this->__('File Size (kilobytes)'),
                'name'  => 'filesize',
                'class' => 'validate-zero-or-greater',
                'note'  => $this->__('Leave empty to disable this validation'),
            )
        );

        $additionalFieldset->addField(
            'allowed_file_extensions',
            'text',
            array(
                'label' => $this->__('Allowed File Extensions'),
                'name'  => 'allowed_file_extensions',
                'note'  => $this->__('Comma separated'),
            )
        );

        $this->setForm($form);
        return $this;
    }

    protected function _setFormValues()
    {
        if ($this->getAttribute()) {
            $form = $this->getForm();
            $form->setValues($this->getAttribute()->getData());
            if (Mage::app()->isSingleStoreMode()) {
                $form->addValues(array('store_ids' => Mage::app()->getStore(true)->getId()));
            }
        }
        return $this;
    }

    public function getAttribute()
    {
        return Mage::registry('current_attribute');
    }
}