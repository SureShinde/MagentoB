<?php

class Bilna_Customer_Model_Api2_Subscription_Rest extends Bilna_Customer_Model_Api2_Subscription
{

    protected function _create(array $data)
    {
        $customer = Mage::getModel('customer/customer')->load($data["customer_id"]);
        $customer->setIsSubscribed(TRUE);
        
        try {
            $customer->save();
        } catch (Mage_Core_Exception $e) {
            $this->_error($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        } catch (Exception $e) {
            $this->_critical(self::RESOURCE_INTERNAL_ERROR);
        }

        $newsletter = Mage::getModel('newsletter/subscriber')->subscribeCustomer($customer);
        
        return $this->_getLocation($newsletter);
    }

    protected function _retrieve()
    {
        $newsletter = $this->_loadCustomerById($this->getRequest()->getParam('id'));
        
        return $newsletter->getData();
    }

    /**
     * Get customers list
     *
     * @return array
     */
    protected function _retrieveCollection()
    {
        $data = $this->_getCollectionForRetrieve()
            ->load()
            ->toArray();
        
        return isset($data['items']) ? $data['items'] : $data;
    }

    /**
     * Load customer by id
     *
     * @param int $id            
     * @throws Mage_Api2_Exception
     * @return Mage_Customer_Model_Customer
     */
    protected function _loadCustomerById($id)
    {
        $customer = Mage::getModel('customer/customer')->load($id);
        
        $newsletter = Mage::getModel('newsletter/subscriber')->loadByCustomer($customer);
        
        return $newsletter;
    }

    protected function _getCollectionForRetrieve()
    {
        /**
         *
         * @var $collection Mage_Customer_Model_Resource_Customer_Collection
         */
        $collection = Mage::getResourceModel('newsletter/subscriber_collection');
        $collection->getSelect();
        
        $collection->addAttributeToSelect(array_keys($this->getAvailableAttributes($this->getUserType(), Mage_Api2_Model_Resource::OPERATION_ATTRIBUTE_READ)));
        
        $this->_applyCollectionModifiers($collection);
        return $collection;
    }
}