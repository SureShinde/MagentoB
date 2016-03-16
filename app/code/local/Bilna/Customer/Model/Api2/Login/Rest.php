<?php
use Mage_Customer_Model_Session as MageSession;

class Bilna_Customer_Model_Api2_Login_Rest extends Bilna_Customer_Model_Api2_Login
{

    protected function _retrieve()
    {
        $session = new MageSession();
        $email = $this->getRequest()->getParam('email');
        $password = $this->getRequest()->getParam('password');
        // $type = $this->getRequest()->getParam('type');
        
        Mage::app()->getStore()->setWebsiteId(1);
        
        // if($type == "web"){
        if (! empty($email) && ! empty($password)) {
            try {
                $session->login($email, $password);
                
                $customer = $session->getCustomer()->getData();
                
                $loggedInCustomer = $this->_loadCustomerById($customer["entity_id"])->getData();
                $loggedInCustomer['username'] = $this->_getUsername($customer["entity_id"]);
                return $loggedInCustomer;
                
            } catch (Mage_Core_Exception $e) {
                switch ($e->getCode()) {
                    case Mage_Customer_Model_Customer::EXCEPTION_EMAIL_NOT_CONFIRMED:
                        $this->_critical("This account is not confirmed");
                        break;
                    case Mage_Customer_Model_Customer::EXCEPTION_INVALID_EMAIL_OR_PASSWORD:
                        $this->_critical($e->getMessage());
                        break;
                    default:
                        $this->_critical($e->getMessage());
                }
            } catch (Exception $e) {
                $this->_critical($e->getMessage());
            }
        } else {
            $this->_critical("Login and password are required.");
        }
        // }
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
    
    //assume username has created on register form of logan app
    protected function _getUsername($customerId = null) 
    {
        $customer = $this->_loadCustomerById($customerId);
        $customerData = $customer->getData();
        
        $username = null;

        if (!isset($customerData['entity_id'])) {
            $this->_critical('No customer account specified.');
        }

        $customerProfile = Mage::getModel('socialcommerce/profile')->load($customerData['entity_id'], 'customer_id');
        $customerProfileData = $customerProfile->getData();

        if (!isset($customerProfileData['entity_id'])) {
            $username = $this->createTemporaryProfile($customerId);
        } else {
            $username = $customerProfileData['username'];
        }
        
        return $username;
    }
    
    public function createTemporaryProfile($customerId = null) 
    {

        $customer = $this->_loadCustomerById($customerId);

        # Temporary username
        $username = Mage::getModel('catalog/product_url')->formatUrlKey($customer->getName());
        $profile = Mage::getModel('socialcommerce/profile')->load($username, 'username')->getData();

        # If username exists, improvise
        if ($profile) {

            for ($i = 1; $i < 101; $i++) {
                $slug = $username . '-' . substr(uniqid(), 7);
                $profile = Mage::getModel('socialcommerce/profile')->load($slug, 'username')->getData();

                if (empty($profile)) {
                    $username = $slug;
                    break;
                }
            }
        }

        # Create new customer profile
        $profile = Mage::getModel('socialcommerce/profile');

        # Assign data
        $profile->setCustomerId($customer->getId());
        $profile->setStatus(1);
        $profile->setWishlist(1);
        $profile->setTemporary(1);
        $profile->setUsername($username);

        $profile->save();

        return $username;

    }
}