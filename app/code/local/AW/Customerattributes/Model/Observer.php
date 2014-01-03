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


class AW_Customerattributes_Model_Observer
{
    const CUSTOMER_ID_REGISTRY_KEY = 'aw_ca_customer_id';

    /**
     * observer on save attributes from customer account edit page
     *
     * @param $observer
     *
     * @return mixed
     */
    public function customerEditPostPostdispatch($observer)
    {
        $controllerAction = $observer->getEvent()->getControllerAction();
        $successMessages = Mage::getSingleton('customer/session')->getMessages()
            ->getItemsByType(Mage_Core_Model_Message::SUCCESS);
        if (!count($successMessages) > 0) {
            return $this;
        }
        $customer = $this->_getCustomer();
        if ($controllerAction->getRequest()->isPost()) {
            $attributeCollection = Mage::helper('aw_customerattributes/customer')
                ->getAttributeCollectionForCustomerEdit($customer)
                ->addEditableByCustomerFilter();
            $errors = $this->_validatePostForCollection($controllerAction, $attributeCollection);
            if (count($errors) > 0) {
                foreach ($errors as $errMsg) {
                    Mage::getSingleton('customer/session')->addError($errMsg);
                }
                Mage::getSingleton('customer/session')->setAWCACustomerFormData(
                    $controllerAction->getRequest()->getPost()
                );
                return $this->_redirect($controllerAction, '*/*/edit');
            }
            //if ALL is OK
            $errors = $this->_savePostForCollection($controllerAction, $attributeCollection);
            if (count($errors) > 0) {
                foreach ($errors as $errMsg) {
                    Mage::getSingleton('customer/session')->addError($errMsg);
                }
                Mage::getSingleton('customer/session')->setAWCACustomerFormData(
                    $controllerAction->getRequest()->getPost()
                );
                return $this->_redirect($controllerAction, '*/*/edit');
            }
        }
        return $this;
    }

    /**
     * observer on save attributes from customer registration page
     *
     * @param $observer
     *
     * @return mixed
     */
    public function customerCreatePostPostdispatch($observer)
    {
        $controllerAction = $observer->getEvent()->getControllerAction();
        $successMessages = Mage::getSingleton('customer/session')->getMessages()
            ->getItemsByType(Mage_Core_Model_Message::SUCCESS);
        if (!count($successMessages) > 0) {
            return $this;
        }
        if ($controllerAction->getRequest()->isPost()) {
            $attributeCollection = Mage::helper('aw_customerattributes/customer')
                ->getAttributeCollectionForCustomerRegister()
                ->addEditableByCustomerFilter();
            $errors = $this->_validatePostForCollection($controllerAction, $attributeCollection);
            if (count($errors) > 0) {
                foreach ($errors as $errMsg) {
                    Mage::getSingleton('customer/session')->addError($errMsg);
                }
                Mage::getSingleton('customer/session')->setAWCACustomerFormData(
                    $controllerAction->getRequest()->getPost()
                );
                return $this->_redirect($controllerAction, '*/*/edit');
            }
            //if ALL is OK
            $errors = $this->_savePostForCollection($controllerAction, $attributeCollection);
            if (count($errors) > 0) {
                foreach ($errors as $errMsg) {
                    Mage::getSingleton('customer/session')->addError($errMsg);
                }
                Mage::getSingleton('customer/session')->setAWCACustomerFormData(
                    $controllerAction->getRequest()->getPost()
                );
                return $this->_redirect($controllerAction, '*/*/edit');
            }
        }
    }

    /**
     * observer on save attributes from BACKEND: customer account edit page
     *
     * @param $observer
     *
     * @return mixed
     */
    public function customerSaveByAdminPostdispatch($observer)
    {
        $controllerAction = $observer->getEvent()->getControllerAction();
        $customer = $this->_getCustomer();
        if ($controllerAction->getRequest()->isPost()) {
            $attributeCollection = Mage::helper('aw_customerattributes/customer')
                ->getAttributeCollectionForCustomerEditByAdmin($customer);
            $errors = $this->_validatePostForCollection($controllerAction, $attributeCollection);
            if (count($errors) > 0) {
                foreach ($errors as $errMsg) {
                    Mage::getSingleton('adminhtml/session')->addError($errMsg);
                }
                Mage::getSingleton('adminhtml/session')->setAWCACustomerFormData(
                    $controllerAction->getRequest()->getPost()
                );

                return $this->_redirect(
                    $controllerAction,
                    '*/*/edit',
                    array(
                        '_current' => true,
                        'id'       => Mage::registry('current_customer')->getId(),
                    )
                );
            }
            //if ALL is OK
            $errors = $this->_savePostForCollection($controllerAction, $attributeCollection);
            if (count($errors) > 0) {
                foreach ($errors as $errMsg) {
                    Mage::getSingleton('adminhtml/session')->addError($errMsg);
                }
                Mage::getSingleton('adminhtml/session')->setAWCACustomerFormData(
                    $controllerAction->getRequest()->getPost()
                );
                return $this->_redirect(
                    $controllerAction,
                    '*/*/edit',
                    array(
                        '_current' => true,
                        'id'       => Mage::registry('current_customer')->getId(),
                    )
                );
            }
        }
        return $this;
    }

    public function customerSaveAfter($observer)
    {
        if ($customer = $observer->getCustomer()) {
            if (Mage::registry(self::CUSTOMER_ID_REGISTRY_KEY)) {
                Mage::unregister(self::CUSTOMER_ID_REGISTRY_KEY);
            }
            Mage::register(self::CUSTOMER_ID_REGISTRY_KEY, $customer->getId());
        }
    }

    /**
     * observer on click on Clear Image in Cache Management
     *
     * @param $observer
     */
    public function cleanImagesCache($observer)
    {
        Mage::helper('aw_customerattributes/image')->cleanImageCache();
    }

    /**
     * validate attributes
     *
     * @param $controllerAction
     * @param $attributeCollection
     *
     * @return array
     */
    protected function _validatePostForCollection($controllerAction, $attributeCollection)
    {
        $errors = array();
        foreach ($attributeCollection as $attribute) {
            $code = AW_Customerattributes_Model_Attribute_TypeAbstract::FRONTEND_ATTRIBUTE_CODE_PREFIX .
                $attribute->getCode();
            $param = $controllerAction->getRequest()->getParam($code, null);
            try {
                $attribute->unpackData()->getTypeModel()->validate($param);
            } catch (Exception $e) {
                $errors[$attribute->getId()] = $e->getMessage();
            }
        }
        return $errors;
    }

    /**
     * save attributes
     *
     * @param $controllerAction
     * @param $attributeCollection
     *
     * @return AW_Customerattributes_Model_Observer
     */
    protected function _savePostForCollection($controllerAction, $attributeCollection)
    {
        $customer = $this->_getCustomer();
        $attributeValueCollection = Mage::helper('aw_customerattributes/customer')
            ->getAttributeValueCollectionForCustomer($customer);
        $valueData = array();
        foreach ($attributeValueCollection as $item) {
            $valueData[$item->getData('attribute_id')] = $item->getId();
        }
        $errors = array();
        foreach ($attributeCollection as $attribute) {
            $code = AW_Customerattributes_Model_Attribute_TypeAbstract::FRONTEND_ATTRIBUTE_CODE_PREFIX .
                $attribute->getCode();
            $param = $controllerAction->getRequest()->getParam($code, null);
            $value = Mage::getModel('aw_customerattributes/value');
            $value->setData(
                array(
                     'attribute_id' => $attribute->getId(),
                     'customer_id'  => $customer->getId(),
                     'value'        => $param,
                )
            );
            if (array_key_exists($attribute->getId(), $valueData)) {
                $value->setId($valueData[$attribute->getId()]);
            }
            $value->setValueType($attribute->getTypeModel()->getValueType());
            try {
                $value->save();
            } catch (Exception $e) {
                $errors[$attribute->getId()] = $e->getMessage();
            }
        }
        return $errors;
    }

    /**
     * Just help function
     *
     * @param $controllerAction
     * @param $path
     * @param $arguments
     */
    private function _redirect($controllerAction, $path, $arguments = array())
    {
        return $controllerAction->getResponse()->setRedirect(Mage::getUrl($path, $arguments));
    }

    /**
     * Get current customer model
     *
     * @return Mage_Customer_Model_Customer
     */
    private function _getCustomer()
    {
        if (Mage::registry('current_customer')) {
            return Mage::registry('current_customer');
        }
        if ($customerId = Mage::registry(self::CUSTOMER_ID_REGISTRY_KEY)) {
            return Mage::getModel('customer/customer')->load($customerId);
        }
        return Mage::helper('customer')->getCustomer();
    }
}