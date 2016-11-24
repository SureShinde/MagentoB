<?php
use Mage_Customer_Model_Session as MageSession;

class Bilna_Customer_Model_Api2_Login_Rest extends Bilna_Customer_Model_Api2_Login
{
    protected function _create(array $data)
    {
        $customer = $this->_loadCustomer($data['email'], $data['password']);
        $this->getResponse()->setHeader('Content-Type', 'application/json');
        $this->getResponse()->setBody(json_encode($customer));
    }

    protected function _retrieveCollection()
    {
        $email = $this->getRequest()->getParam('email');
        $password = $this->getRequest()->getParam('password');
        $customer = $this->_loadCustomer($email, $password);
        return $customer;
    }

    protected function _loadCustomer($email, $password)
    {
        if (empty($email) || empty($password)) {
            $this->_critical('Login and password are required.');
        }

        try {
            Mage::app()->getStore()->setWebsiteId(1);
            $session = new MageSession();
            $session->login($email, $password);
            $customer = $session->getCustomer()->getData();

            $loggedInCustomer = $this->_loadCustomerById($customer['entity_id'])->getData();
            $loggedInCustomer['username'] = $this->_getUsername($customer['entity_id']);
            return $loggedInCustomer;
        } catch (Mage_Core_Exception $e) {
            switch ($e->getCode()) {
                case Mage_Customer_Model_Customer::EXCEPTION_EMAIL_NOT_CONFIRMED:
                    $this->_critical('This account is not confirmed');
                case Mage_Customer_Model_Customer::EXCEPTION_INVALID_EMAIL_OR_PASSWORD:
                    $this->_critical($e->getMessage());
                default:
                    $this->_critical($e->getMessage());
            }
        } catch (Exception $e) {
            $this->_critical($e->getMessage());
        }
    }

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

    protected function _getUsername($customerId = null)
    {
        $customer = $this->_loadCustomerById($customerId);
        $customerData = $customer->getData();

        if (!isset($customerData['entity_id'])) {
            $this->_critical('No customer account specified.');
        }

        // assume username has been set on registration form
        $customerProfile = Mage::getModel('socialcommerce/profile')->load($customerData['entity_id'], 'customer_id');
        $customerProfileData = $customerProfile->getData();

        if (!isset($customerProfileData['entity_id'])) {
            $username = Mage::helper('socialcommerce')->createTemporaryProfile($customer);
        } else {
            $username = $customerProfileData['username'];
        }

        return $username;
    }
}
