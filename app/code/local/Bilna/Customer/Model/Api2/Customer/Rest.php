<?php

abstract class Bilna_Customer_Model_Api2_Customer_Rest extends Bilna_Customer_Model_Api2_Customer
{
    const XML_PATH_REGISTER_EMAIL_IDENTITY = 'customer/create_account/email_identity';

    /**
     * Create customer
     *
     * @param array $data            
     * @return string
     */
    protected function _create(array $data)
    {
        /**
         * @var $validator Mage_Api2_Model_Resource_Validator_Eav
         */
        $validator = Mage::getResourceModel('api2/validator_eav', array(
            'resource' => $this
        ));
        
        $extra["password_hash"] = $this->_getHelper('core')->getHash($data["password"], Mage_Admin_Model_User::HASH_SALT_LENGTH);
        if(isset($data["newsletter"]) && $data["newsletter"]==1) $extra["is_subscribed"] = true;

        $data = $validator->filter($data);
        $data = array_merge($data, $extra);
        unset($extra);
        
        if (! $validator->isValidData($data)) {
            foreach ($validator->getErrors() as $error) {
                $this->_error($error, Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
            }
            $this->_critical(self::RESOURCE_DATA_PRE_VALIDATION_ERROR);
        }
        
        /**
         * @var $customer Mage_Customer_Model_Customer
         */
        $customer = Mage::getModel('customer/customer');
        $customer->setData($data);
        
        try {
            $customer->save();
            $this->_dispatchRegisterSuccess($customer);
            $this->_successProcessRegistration($customer);
        } catch (Mage_Core_Exception $e) {
            $this->_error($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        } catch (Exception $e) {
            $this->_critical(self::RESOURCE_INTERNAL_ERROR);
        }
        
        return $this->_getLocation($customer);
    }

    /**
     * Retrieve information about customer
     *
     * @throws Mage_Api2_Exception
     * @return array
     */
    protected function _retrieve()
    {
        /**
         * @var $customer Mage_Customer_Model_Customer
         */
        $customer = $this->_loadCustomerById($this->getRequest()
            ->getParam('id'));
        return $customer->getData();
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
     * Update customer
     *
     * @param array $data            
     * @throws Mage_Api2_Exception
     */
    protected function _update(array $data)
    {
        /**
         * @var $customer Mage_Customer_Model_Customer
         */
        $customer = $this->_loadCustomerById($this->getRequest()
            ->getParam('id'));
        /**
         * @var $validator Mage_Api2_Model_Resource_Validator_Eav
         */
        $validator = Mage::getResourceModel('api2/validator_eav', array(
            'resource' => $this
        ));
        
        if($data["password"]) $extra["password_hash"] = $this->_getHelper('core')->getHash($data["password"], Mage_Admin_Model_User::HASH_SALT_LENGTH);

        $data = $validator->filter($data);
        if($extra){
            $data = array_merge($data, $extra);
            unset($extra);
        }
        
        unset($data['website_id']); // website is not allowed to change
        
        if (! $validator->isValidData($data, true)) {
            foreach ($validator->getErrors() as $error) {
                $this->_error($error, Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
            }
            $this->_critical(self::RESOURCE_DATA_PRE_VALIDATION_ERROR);
        }
        
        $customer->addData($data);
        
        try {
            $customer->save();
        } catch (Mage_Core_Exception $e) {
            $this->_error($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        } catch (Exception $e) {
            $this->_critical(self::RESOURCE_INTERNAL_ERROR);
        }
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
        /**
         * @var $customer Mage_Customer_Model_Customer
         */
        $customer = Mage::getResourceModel('customer/customer_collection');
        $customer   ->getSelect()
                    ->joinLeft(array("a" => "newsletter_subscriber"), "e.entity_id = a.customer_id AND a.subscriber_status = 1", array("newsletter" => "subscriber_email"))
                    ->where('e.entity_id = '.$id)
                    ->limit(1);

        $customer->addAttributeToSelect(array_keys($this->getAvailableAttributes($this->getUserType(), Mage_Api2_Model_Resource::OPERATION_ATTRIBUTE_READ)));
        
        $this->_applyCollectionModifiers($customer);
        
        return $customer->getFirstItem();
    }

    /**
     * Retrieve collection instances
     *
     * @return Mage_Customer_Model_Resource_Customer_Collection
     */
    protected function _getCollectionForRetrieve()
    {
        /**
         * @var $collection Mage_Customer_Model_Resource_Customer_Collection
         */
        $collection = Mage::getResourceModel('customer/customer_collection');
        $collection ->getSelect()
                    ->joinLeft(array("a" => "newsletter_subscriber"), "e.entity_id = a.customer_id AND a.subscriber_status = 1", array("newsletter" => "subscriber_email"));
        
        $collection->addAttributeToSelect(array_keys($this->getAvailableAttributes($this->getUserType(), Mage_Api2_Model_Resource::OPERATION_ATTRIBUTE_READ)));

        $this->_applyCollectionModifiers($collection);

        return $collection;
    }

    protected function _getHelper($helperName)
    {
        return Mage::helper($helperName);
    }

    /**
     * Dispatch Event
     *
     * @param Mage_Customer_Model_Customer $customer
     */
    protected function _dispatchRegisterSuccess($customer) {
        Mage::dispatchEvent('customer_register_success', array ('account_controller' => $this, 'customer' => $customer));
        $this->_customerSaveAfter($customer);
    }
    
    protected function _successProcessRegistration(Mage_Customer_Model_Customer $customer) {
        $customer->sendNewAccountEmail();
    }
    
    protected static $_customerNotSet = true;
    protected function _customerSaveAfter($customer) {
        if ($this->_isModuleDisabled($this->_getStore()->getStoreId())) {
            return;
        }

        $summary = Mage::getModel('points/summary')->loadByCustomer($customer);

        /* Check if customer saved for the first time (is new) */
        if ($customer->getCreatedAt() == $customer->getUpdatedAt() && self::$_customerNotSet) {
            self::$_customerNotSet = false;
            $isSubscribedByDefault = Mage::helper('points/config')->getIsSubscribedByDefault();

            if ($isSubscribedByDefault) {
                $summary = Mage::getModel('points/summary')->loadByCustomer($customer);
                $summary->setBalanceUpdateNotification(1)
                    ->setPointsExpirationNotification(1)
                    //->setPointsForRegistrationGranted(1)
                    ->setUpdateDate(true)
                    ->save();
            }

            $pointsForRegistration = Mage::helper('points/config')->getPointsForRegistration();
            Mage::getModel('points/api')->addTransaction($pointsForRegistration, 'customer_register', $customer, $customer);
        }

        if (is_null($customer->getConfirmation())) {
            $this->_findAffiliateForCustomer($customer);
        }
    }
    
    protected function _isModuleDisabled($storeId) {
        if (!Mage::helper('points/config')->isPointsEnabled($storeId) || Mage::getStoreConfig('advanced/modules_disable_output/AW_Points', $storeId)) {
            return true;
        }
    }
    
    protected function _findAffiliateForCustomer($customer) {
        return;
    }
}
