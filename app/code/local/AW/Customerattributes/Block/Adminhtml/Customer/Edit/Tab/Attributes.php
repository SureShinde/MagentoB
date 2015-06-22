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


class AW_Customerattributes_Block_Adminhtml_Customer_Edit_Tab_Attributes
    extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    protected $_valueData = null;
    protected $_defaultValueData = null;

    protected function _construct()
    {
        parent::_construct();
        $this->setAfter('account');
    }

    public function getTabLabel()
    {
        return $this->__('Personal Customer Attributes');
    }

    public function getTabTitle()
    {
        return $this->__('Personal Customer Attributes');
    }

    public function canShowTab()
    {
        return $this->getId() ? true : false;
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
            array('legend' => $this->__('Personal Customer Attributes'))
        );
        $this->_defaultValueData = array();
        foreach ($this->getAttributeCollection() as $attribute) {
            $this->_defaultValueData[$this->_getCodeWithPrefix($attribute->getCode())] = $attribute->getDefaultValue();
            $renderer = $attribute->unpackData()->getTypeModel()->getBackendFormRenderer();
            if (!is_null($renderer->getFieldTypeRenderer())) {
                $generalFieldset->addType($renderer->getFieldType(), $renderer->getFieldTypeRenderer());
            }
            $generalFieldset->addField(
                $renderer->getFieldId(), $renderer->getFieldType(), $renderer->getFieldProperties()
            );
        }

        $this->setForm($form);
        return $this;
    }

    protected function _setFormValues()
    {
        $values = array();
        $sessionValues = Mage::getSingleton('adminhtml/session')->getAWCACustomerFormData();
        Mage::getSingleton('adminhtml/session')->setAWCACustomerFormData(null);
        foreach ($this->getAttributeCollection() as $attribute) {
            $value = $this->getValueByAttributeId($attribute->getId());
            if (!is_null($value)) {
                $attributeCodeWithPrefix = $this->_getCodeWithPrefix($attribute->getCode());
                if (is_array($sessionValues) && array_key_exists($attributeCodeWithPrefix, $sessionValues)) {
                    if (!in_array($attribute->getType(), array('image', 'attachment'))) {
                        $values[$attributeCodeWithPrefix] = $sessionValues[$attributeCodeWithPrefix];
                    }
                } else {
                    $values[$attributeCodeWithPrefix] = $value;
                }
            }
        }

        $emptyAttributeCodes = array_diff(array_keys($this->_defaultValueData), array_keys($values));
        foreach ($emptyAttributeCodes as $attributeCode) {
            if (is_array($sessionValues) && array_key_exists($attributeCode, $sessionValues)) {
                if (!is_array($sessionValues[$attributeCode])) {
                    $values[$attributeCode] =  $sessionValues[$attributeCode];
                } elseif (array_key_exists('value', $sessionValues[$attributeCode])) {
                    $values[$attributeCode] =  $sessionValues[$attributeCode]['value'];
                } else {
                    $values[$attributeCode] = $sessionValues[$attributeCode];
                }
            } else {
                $values[$attributeCode] = $this->_defaultValueData[$attributeCode];
            }
        }
        $form = $this->getForm();
        $form->setValues($values);
        return $this;
    }

    protected function getAttributeCollection()
    {
        $customer = Mage::registry('current_customer');
        return Mage::helper('aw_customerattributes/customer')->getAttributeCollectionForCustomerEditByAdmin($customer);
    }

    public function getValueByAttributeId($attributeId)
    {
        if (is_null($this->_valueData)) {
            $this->_valueData = array();
            $customer = Mage::registry('current_customer');
            $collection = Mage::helper('aw_customerattributes/customer')
                ->getAttributeValueCollectionForCustomer($customer);
            foreach ($collection as $item) {
                $this->_valueData[$item->getData('attribute_id')] = $item->getData('value');
            }
        }
        return isset($this->_valueData[$attributeId]) ? $this->_valueData[$attributeId] : null;
    }

    private function _getCodeWithPrefix($code)
    {
        return AW_Customerattributes_Model_Attribute_TypeAbstract::FRONTEND_ATTRIBUTE_CODE_PREFIX . $code;
    }
}