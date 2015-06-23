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

class AW_Customerattributes_Adminhtml_Customer_AttributeController extends Mage_Adminhtml_Controller_Action
{
    protected function _initAttribute($requestParamName = 'id')
    {
        $attributeId = (int)$this->getRequest()->getParam($requestParamName, 0);
        $attribute = Mage::getModel('aw_customerattributes/attribute');
        if ($attributeId > 0) {
            $attribute->load($attributeId);
        }
        if ($data = Mage::getSingleton('adminhtml/session')->getAttributeFormData()) {
            $attribute->addData($data);
            Mage::getSingleton('adminhtml/session')->setAttributeFormData(null);
        }
        Mage::register('current_attribute', $attribute);
        return $this;
    }

    public function indexAction()
    {
        $this->_title($this->__('Customer Attributes'))->_title($this->__('Manage Attributes'));
        $this->loadLayout();
        $this->_setActiveMenu('customer/aw_customerattributes');
        $this->renderLayout();
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        try {
            $this->_initAttribute();
            $attribute = Mage::registry('current_attribute');
            if (!is_null($attribute->getData('default_value'))) {
                $defaultValueField = 'default_value_' . $attribute->getType();
                $attribute->setData($defaultValueField, $attribute->getData('default_value'));
            }
            if ($attribute->hasData('validation_rules')) {
                $attribute->addData($attribute->getData('validation_rules'));
            }
        } catch (Exception $e) {
            $this->_getSession()->addError($this->__($e->getMessage()));
            return $this->_redirect('*/*/index');
        }

        $this->_title($this->__('Customer Attributes'));
        if (Mage::registry('current_attribute')->getId()) {
            $this->_title($this->__('Edit Attributes'));
        } else {
            $this->_title($this->__('Create Attributes'));
        }
        $this->loadLayout();
        $this->_setActiveMenu('customer/aw_customerattributes');
        $this->renderLayout();
    }

    public function saveAction()
    {
        try {
            $this->_initAttribute();
            $data = $this->getRequest()->getParams();
            $attribute = Mage::registry('current_attribute');
            $attribute->addData($data);
            if (!isset($data['store_ds'])) {
                $attribute->setData('store_ds', array());
            }
            if (!isset($data['customer_groups'])) {
                $attribute->setData('customer_groups', array());
            }
            if (!isset($data['display_on'])) {
                $attribute->setData('display_on', array());
            }
            $this->_initDefaultValue();
            $this->_initValidationRules();
            $attribute->save();
            $this->_saveStoreLabels();
            $this->_saveStoreOptions();
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_getSession()->addError($this->__('Unable save attribute'));
            $data = $this->getRequest()->getParams();
            Mage::getSingleton('adminhtml/session')->setAttributeFormData($data);
            return $this->_redirect('*/*/edit', array('_current' => true, 'id' => $data['id']));
        }
        $this->_getSession()->addSuccess(
            $this->__(
                'Attribute "%s" has been successfully saved',
                htmlspecialchars(Mage::registry('current_attribute')->getLabel())
            )
        );
        Mage::getSingleton('adminhtml/session')->setAttributeFormData(null);
        if ($this->getRequest()->getParam('back')) {
            return $this->_redirect('*/*/edit', array('_current' => true, 'id' => $attribute->getId()));
        }
        return $this->_redirect('*/*/index');
    }

    public function validateAction()
    {
        $response = new Varien_Object();
        $response->setError(false);

        $attributeCode = $this->getRequest()->getParam('code');
        $attributeId = $this->getRequest()->getParam('id', 0);
        if ($attributeId) {
            $attribute = Mage::getModel('aw_customerattributes/attribute')->load($attributeId);
        } else {
            $attribute = Mage::getModel('aw_customerattributes/attribute')->loadByCode($attributeCode);
        }

        if ($attribute->getId() && !$attributeId) {
            Mage::getSingleton('adminhtml/session')
                ->addError(
                    Mage::helper('aw_customerattributes')->__('Attribute with the same code already exists')
                );
            $this->_initLayoutMessages('adminhtml/session');
            $response->setError(true);
            $response->setMessage($this->getLayout()->getMessagesBlock()->getGroupedHtml());
        } else {
            $attribute->addData($this->getRequest()->getPost());
            Mage::register('current_attribute', $attribute);
            $this->_initDefaultValue();
            $this->_initValidationRules();
            try {
                $defaultValue = $attribute->getDefaultValue();
                if (!empty($defaultValue)) {
                    $attribute->getTypeModel()->validate($defaultValue);
                }
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($this->__('Default value: %s', $e->getMessage()));
                $this->_initLayoutMessages('adminhtml/session');
                $response->setError(true);
                $response->setMessage($this->getLayout()->getMessagesBlock()->getGroupedHtml());
            }
        }
        $this->getResponse()->setBody($response->toJson());
    }

    public function deleteAction()
    {
        try {
            $this->_initAttribute();
            $attribute = Mage::registry('current_attribute');
            $attributeLabel = $attribute->getLabel();
            $attribute->delete();
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_getSession()->addError($this->__('Unable remove attribute'));
            return $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id', 0)));
        }
        $this->_getSession()->addSuccess(
            $this->__('Attribute "%s" has been successfully deleted', htmlspecialchars($attributeLabel))
        );
        return $this->_redirect('*/*/index');
    }

    public function massDeleteAction()
    {
        $attributeIds = (array)$this->getRequest()->getParam('attribute');
        try {
            foreach ($attributeIds as $attributeId) {
                $attribute = Mage::getModel('aw_customerattributes/attribute')->load($attributeId);
                $attribute->delete();
            }
            $this->_getSession()->addSuccess(
                $this->__('Total of %d record(s) have been deleted.', count($attributeIds))
            );
        } catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        $this->_redirect('*/*/index');
    }

    public function massStatusAction()
    {
        $attributeIds = (array)$this->getRequest()->getParam('attribute');
        $status = (int)$this->getRequest()->getParam('status');

        try {
            foreach ($attributeIds as $attributeId) {
                $attribute = Mage::getModel('aw_customerattributes/attribute')->load($attributeId);
                $attribute->setIsEnabled($status);
                $attribute->save();
            }
            $this->_getSession()->addSuccess(
                $this->__('Total of %d record(s) have been updated.', count($attributeIds))
            );
        } catch (Mage_Core_Model_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addException(
                $e, $this->__('An error occurred while updating the attribute(s) status.')
            );
        }
        $this->_redirect('*/*/index');
    }

    /**
     * Init default value
     *
     * @return $this
     */
    protected function _initDefaultValue()
    {
        $attribute = Mage::registry('current_attribute');
        $defaultValueField = 'default_value_' . $attribute->getType();
        if (!is_null($attribute->getData($defaultValueField))) {
            $defaultValue = $attribute->getData($defaultValueField);
            if ($attribute->getType() == 'date') {
                $defaultValue = $this->_presaveDateValue($defaultValue);
            }
            $attribute->setData('default_value', $defaultValue);
        }
        return $this;
    }

    /**
     * Init validation rules
     *
     * @return $this
     */
    public function _initValidationRules()
    {
        $data = $this->getRequest()->getParams();
        $attribute = Mage::registry('current_attribute');
        $validationRules = array();
        $validationRules['is_required'] = $data['is_required'];
        switch ($attribute->getData('type')) {
            case 'text':
                $validationRules['minimum_text_length'] = "";
                if (strlen($data['minimum_text_length']) > 0) {
                    $validationRules['minimum_text_length'] = intval($data['minimum_text_length']);
                }
                $validationRules['maximum_text_length'] = "";
                if (strlen($data['maximum_text_length']) > 0) {
                    $validationRules['maximum_text_length'] = intval($data['maximum_text_length']);
                }
                $validationRules['input_validation'] = $data['input_validation'];
                $validationRules['is_unique'] = $data['is_unique'];
                break;
            case 'textarea':
                $validationRules['minimum_text_length'] = "";
                if (strlen($data['minimum_text_length']) > 0) {
                    $validationRules['minimum_text_length'] = intval($data['minimum_text_length']);
                }
                $validationRules['maximum_text_length'] = "";
                if (strlen($data['maximum_text_length']) > 0) {
                    $validationRules['maximum_text_length'] = intval($data['maximum_text_length']);
                }
                $validationRules['input_validation'] = $data['input_validation'];
                break;
            case 'date':
                break;
            case 'yesno':
                break;
            case 'multipleselect':
                break;
            case 'dropdown':
                break;
            case 'image':
                $validationRules['image_height'] = "";
                if (strlen($data['image_height']) > 0) {
                    $validationRules['image_height'] = intval($data['image_height']);
                }
                $validationRules['image_width'] = "";
                if (strlen($data['image_width']) > 0) {
                    $validationRules['image_width'] = intval($data['image_width']);
                }
                $validationRules['filesize'] = "";
                if (strlen($data['filesize']) > 0) {
                    $validationRules['filesize'] = intval($data['filesize']);
                }
                break;
            case 'attachment':
                $validationRules['filesize'] = "";
                if (strlen($data['filesize']) > 0) {
                    $validationRules['filesize'] = intval($data['filesize']);
                }
                $validationRules['allowed_file_extensions'] = $data['allowed_file_extensions'];
                break;
            default:
                break;
        }
        $attribute->setData('validation_rules', $validationRules);
        return $this;
    }

    /**
     * Save store labels
     */
    protected function _saveStoreLabels()
    {
        $data = $this->getRequest()->getParams();
        $attribute = Mage::registry('current_attribute');
        $savedLabels = array();
        foreach ($attribute->getLabelCollection() as $label) {
            $label->setValue($data['frontend_label'][$label->getStoreId()]);
            if (strlen(trim($label->getValue())) > 0) {
                $label->save();
            } else {
                $label->delete();
            }
            $savedLabels[$label->getStoreId()] = $data['frontend_label'][$label->getStoreId()];
        }
        foreach (array_diff($data['frontend_label'], $savedLabels) as $storeId => $labelValue) {
            if (strlen(trim($labelValue)) === 0) {
                continue;
            }
            $labelModel = Mage::getModel('aw_customerattributes/label');
            $labelModel->setData(
                array(
                    'attribute_id' => $attribute->getId(),
                    'store_id'     => $storeId,
                    'value'        => $labelValue,
                )
            );
            $labelModel->save();
        }
        return $this;
    }

    /**
     *
     */
    protected function _saveStoreOptions()
    {
        $data = $this->getRequest()->getParams();
        if (isset($data['option'])) {
            $attribute = Mage::registry('current_attribute');
            foreach (array_reverse($data['option']['value'], true) as $optionId => $optionValue) {
                $option = Mage::getModel('aw_customerattributes/option');
                $option->load($optionId);
                if ($data['option']['delete'][$optionId]) {
                    $option->delete();
                } else {
                    $option->setSortOrder($data['option']['order'][$optionId]);
                    $option->setAttributeId($attribute->getId());
                    $option->save();
                    if (isset($data['default']) && in_array($optionId, $data['default'])) {
                        $index = array_search($optionId, $data['default']);
                        $data['default'][$index] = $option->getId();
                    }
                    // Save option labels
                    $savedValues = array();
                    foreach ($option->getValueCollection() as $value) {
                        $value->setValue($optionValue[$value->getStoreId()]);
                        $value->save();
                        $savedValues[$value->getStoreId()] = $optionValue[$value->getStoreId()];
                    }
                    foreach (array_diff($optionValue, $savedValues) as $storeId => $value) {
                        $optionValueModel = Mage::getModel('aw_customerattributes/option_value');
                        $optionValueModel->setData(
                            array(
                                'option_id' => $option->getId(),
                                'store_id'  => $storeId,
                                'value'     => $value,
                            )
                        );
                        $optionValueModel->save();
                    }
                }
            }
            // Update default value
            if (isset($data['default'])) {
                $deletedDefaultOptions = array();
                foreach ($data['default'] as $key => $optionId) {
                    if (
                        array_key_exists($optionId, $data['option']['delete'])
                        && $data['option']['delete'][$optionId]
                    ) {
                        $deletedDefaultOptions[] = $data['default'][$key];
                    }
                }
                $data['default'] = array_diff($data['default'], $deletedDefaultOptions);
                $defaultValue = implode(',', $data['default']);
            } else {
                $defaultValue = '';
            }
            $attribute->setData('default_value', $defaultValue);
            $attribute->save();
        }
        return $this;
    }

    protected function _title($text = null, $resetIfExists = false)
    {
        if (Mage::helper('aw_customerattributes')->checkMageVersion()) {
            return parent::_title($text, $resetIfExists);
        }
        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('customer/aw_customerattributes/manage_attributes');
    }

    private function _presaveDateValue($date)
    {
        if (strlen(trim($date)) > 0) {
            $zDate = Mage::app()->getLocale()->date(
                $date,
                Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT)
            );
            $date = $zDate->toString(Varien_Date::DATE_INTERNAL_FORMAT);
        }
        return $date;
    }
}