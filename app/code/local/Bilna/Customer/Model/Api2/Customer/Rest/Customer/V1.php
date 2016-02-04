<?php

class Bilna_Customer_Model_Api2_Customer_Rest_Customer_V1 extends Bilna_Customer_Model_Api2_Customer_Rest
{

    /**
     * Is customer has rights to retrieve/update customer item
     *
     * @param int $customerId            
     * @throws Mage_Api2_Exception
     * @return bool
     */
    protected function _isOwner($customerId)
    {
        if ($this->getApiUser()->getUserId() !== $customerId) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        return true;
    }

    /**
     * Retrieve information about customer
     *
     * @throws Mage_Api2_Exception
     * @return array
     */
    protected function _retrieve()
    {
        if ($this->_isOwner($this->getRequest()
            ->getParam('id'))) {
            return parent::_retrieve();
        }
    }

    /**
     * Retrieve collection with only current customer instance
     *
     * @return Mage_Customer_Model_Resource_Customer_Collection
     */
    protected function _getCollectionForRetrieve()
    {
        return parent::_getCollectionForRetrieve()->addAttributeToFilter('entity_id', $this->getApiUser()
            ->getUserId());
    }

    /**
     * Update customer
     *
     * @param array $data            
     * @throws Mage_Api2_Exception
     */
    protected function _update(array $data)
    {
        if ($this->_isOwner($this->getRequest()
            ->getParam('id'))) {
            parent::_update($data);
        }
    }

    /**
     * Update customers
     *
     * @param array $data            
     * @throws Mage_Api2_Exception
     */
    protected function _multiUpdate(array $data)
    {
        $this->_critical(self::RESOURCE_METHOD_NOT_ALLOWED, Mage_Api2_Model_Server::HTTP_FORBIDDEN);
    }
}
