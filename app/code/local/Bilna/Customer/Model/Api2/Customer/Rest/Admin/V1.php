<?php

class Bilna_Customer_Model_Api2_Customer_Rest_Admin_V1 extends Bilna_Customer_Model_Api2_Customer_Rest {
    /**
     * Create customer
     *
     * @param array $data
     * @return string
     */
    protected function _create(array $data) {
        /**
         * @var $validator Mage_Api2_Model_Resource_Validator_Eav
         */
        $validator = Mage::getResourceModel('api2/validator_eav', ['resource' => $this]);
        $extra = [];
        $extra['password_hash'] = $this->_getHelper('core')->getHash($data['password'], Mage_Admin_Model_User::HASH_SALT_LENGTH);

        if (isset ($data['newsletter']) && $data['newsletter'] == 1) {
            $extra['is_subscribed'] = true;
        }

        $username = $data['username'];
        $data = $validator->filter($data);
        $data = array_merge($data, $extra);
        unset ($extra);

        if (!$validator->isValidData($data)) {
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
            /**
             * process username from register form logan
             * start : add username on register
             */
            if (!preg_match ('/^[a-zA-Z0-9_.-]+$/', $username)) {
                $this->_error('Username ' . $username . ' contains invalid character. Only letters (a-z), numbers (0-9), periods (.), dashs (-), and underscores (_) are allowed', Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
                $this->_critical(self::RESOURCE_DATA_PRE_VALIDATION_ERROR);
            }

            $usernameAvailable = Mage::helper('socialcommerce')->checkUsernameAvailable($username);

            if (!$usernameAvailable) {
                $this->_error('Username ' . $username . ' already used by someone else. Please choose another username', Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
                $this->_critical(self::RESOURCE_DATA_PRE_VALIDATION_ERROR);
            }
            else {
                $customer->save();
                $profile = Mage::getModel('socialcommerce/profile');

                //- Assign data
                $profile->setCustomerId($customer->getId());
                $profile->setStatus(1);
                $profile->setWishlist(1);
                $profile->setTemporary(0);
                $profile->setUsername($username);
                $profile->save();
            }

            $this->_dispatchRegisterSuccess($customer);
            $this->_successProcessRegistration($customer);
        }
        catch (Mage_Core_Exception $e) {
            $this->_error($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        }
        catch (Exception $e) {
            $this->_critical(self::RESOURCE_INTERNAL_ERROR);
        }

        return $this->_getLocation($customer);
    }

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
        $verificationData = $this->_getVerificationData($this->getRequest()->getParam('id'));
        $data['mobile_number'] = $verificationData['mobile_number'];
        $data['verified_date'] = $verificationData['verified_date'];
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
