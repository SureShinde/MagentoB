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

class AW_Customerattributes_Helper_Customer extends Mage_Core_Helper_Data
{
    /**
     * @return AW_Customerattributes_Model_Resource_Attribute_Collection
     */
    public function getAttributeCollectionForCustomerRegister()
    {
        $collection = Mage::getModel('aw_customerattributes/attribute')->getCollection();
        $collection
            ->addIsEnabledFilter()
            ->addStoreFilter(Mage::app()->getStore()->getId())
            ->addCustomerGroupFilter(Mage_Customer_Model_Group::NOT_LOGGED_IN_ID)
            ->addDisplayOnCreateAccountPageFilter()
            ->sortBySortOrder();
        return $collection;
    }

    /**
     * @param Mage_Customer_Model_Customer $customer
     *
     * @return AW_Customerattributes_Model_Resource_Attribute_Collection
     */
    public function getAttributeCollectionForCustomerEdit(Mage_Customer_Model_Customer $customer)
    {
        $collection = Mage::getModel('aw_customerattributes/attribute')->getCollection();
        $collection
            ->addIsEnabledFilter()
            ->addStoreFilter(Mage::app()->getStore()->getId())
            ->addCustomerGroupFilter($customer->getGroupId())
            ->addDisplayOnCustomerAccountPageFilter()
            ->sortBySortOrder();
        return $collection;
    }

    /**
     * @param Mage_Customer_Model_Customer $customer
     *
     * @return AW_Customerattributes_Model_Resource_Attribute_Collection
     */
    public function getAttributeCollectionForCustomerEditByAdmin(Mage_Customer_Model_Customer $customer)
    {
        $collection = Mage::getModel('aw_customerattributes/attribute')->getCollection();
        $collection
            ->addIsEnabledFilter()
            ->addCustomerGroupFilter($customer->getGroupId())
            ->sortBySortOrder();
        return $collection;
    }

    /**
     * @param Mage_Customer_Model_Customer $customer
     *
     * @return AW_Customerattributes_Model_Resource_Value_Collection
     */
    public function getAttributeValueCollectionForCustomer(Mage_Customer_Model_Customer $customer)
    {
        $collection = Mage::getModel('aw_customerattributes/value')->getCollection();
        $collection->addCustomerFilter($customer->getId());
        return $collection;
    }

    /**
     * @return AW_Customerattributes_Model_Resource_Attribute_Collection
     */
    public function getAttributeCollectionForCustomerGrid()
    {
        $collection = Mage::getModel('aw_customerattributes/attribute')->getCollection();
        $collection
            ->addIsEnabledFilter()
            ->addIsDisplayInGridFilter()
            ->sortBySortOrder(Varien_Data_Collection::SORT_ORDER_DESC);
        return $collection;
    }

    public function addAttributesToCustomerCollection(Mage_Customer_Model_Entity_Customer_Collection $collection)
    {
        return Mage::getResourceModel('aw_customerattributes/attribute_collection')
            ->addAttributesToCustomerCollection($collection);
    }

    public function getCustomer()
    {
        if (Mage::registry('current_customer') && Mage::registry('current_customer')->getId()) {
            return Mage::registry('current_customer');
        }
        $customer = Mage::helper('customer')->getCustomer();
        if ($customer && $customer->getId()) {
            return $customer;
        }
        if ($customerId = Mage::registry(AW_Customerattributes_Model_Observer::CUSTOMER_ID_REGISTRY_KEY)) {
            return Mage::getModel('customer/customer')->load($customerId);
        }
        return null;
    }
}