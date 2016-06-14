<?php

class Bilna_Customer_Model_Api2_Customer_Rest_Admin_V1 extends Bilna_Customer_Model_Api2_Customer_Rest
{

    /**
     * Retrieve information about customer
     * Add last logged in datetime
     *
     * @throws Mage_Api2_Exception
     * @return array
     */
    protected function _retrieve()
    {
        /**
         * @var $log Mage_Log_Model_Customer
         */
        $log = Mage::getModel('log/customer');
        $log->loadByCustomer($this->getRequest()
            ->getParam('id'));
        
        $data = parent::_retrieve();
        $data['is_confirmed'] = (int) ! (isset($data['confirmation']) && $data['confirmation']);
        $data['username'] = $this->_getUsername($this->getRequest()->getParam('id'));
        
        $lastLoginAt = $log->getLoginAt();
        if (null !== $lastLoginAt) {
            $data['last_logged_in'] = $lastLoginAt;
        }
        return $data;
    }

    /**
     * Delete customer
     */
    protected function _delete()
    {
        /**
         * @var $customer Mage_Customer_Model_Customer
         */
        $customer = parent::_loadCustomerById($this->getRequest()->getParam('id'));
        
        try {
            $customer->delete();
        } catch (Mage_Core_Exception $e) {
            $this->_critical($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        } catch (Exception $e) {
            $this->_critical(self::RESOURCE_INTERNAL_ERROR);
        }
    }
    
    protected function _retrieveCollection() {
        $result = $this->_getRetrieveCollection();
        
        if (!$result) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        
        return $result;
    }
    
    protected function _getRetrieveCollection() {
        $result = array ();
        $customerId = $this->getRequest()->getParam('id');
        $email = $this->getRequest()->getParam('email');
        $result = [];
        $newsletter = null;
        
        try {
            if (!empty($customerId)) {
                $customer = parent::_loadCustomerById($customerId);
                $result = $customer;
                $result['username'] = $this->_getUsername($customerId);            
            } else {
                $customer = Mage::getModel('customer/customer'); 
                $customer->setWebsiteId(1); 
                $customer->loadByEmail($email);
                
                $result['entity_id'] = $customer->getId();
                $result['website_id'] = $customer->getWebsiteId();
                $result['email'] = $customer->getEmail();
                $result['group_id'] = $customer->getGroupId();
                $result['created_at'] = $customer->getCreatedAt();
                $result['is_active'] = $customer->getIsActive();
                $result['disable_auto_group_change'] = (empty($customer->getDisabledAutoGroupChange()) ? '0' : $customer->getDisabledAutoGroupChange());
                $subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($email);
                if ($subscriber->getId()) {
                    $newsletter = $subscriber->getEmail();
                }
                $result['newsletter'] = $newsletter;
                $result['firstname'] = $customer->getFirstname();
                $result['lastname'] = $customer->getLastname();
                $result['created_in'] = $customer->getCreatedIn();
                $result['gender'] = $customer->getGender();
                $result['netsuite_internal_id'] = $customer->getNetsuiteInternalId();
                $result['dob'] = $customer->getDob();
                $result['username'] = $this->_getUsername($customer->getId());
                $log = Mage::getModel('log/customer');
                $log->loadByCustomer($customer->getId());
                $lastLoginAt = $log->getLoginAt();
                if (null !== $lastLoginAt) {
                    $result['last_logged_in'] = $lastLoginAt;
                }
            }
        } catch (Exception $ex) {
            $this->_critical($ex->getMessage());
        }        
        
        return $result;
    }
}
