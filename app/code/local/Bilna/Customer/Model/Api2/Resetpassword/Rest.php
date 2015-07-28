<?php

abstract class Bilna_Customer_Model_Api2_Resetpassword_Rest extends Bilna_Customer_Model_Api2_Resetpassword
{

    protected function _create(array $data)
    {
        $customer = Mage::getModel('customer/customer')
            ->setWebsiteId(1)
            ->loadByEmail($data["email"]);

        if ($customer->getId()) {
            try {
                $newResetPasswordLinkToken =  Mage::helper('customer')->generateResetPasswordLinkToken();
                $customer->changeResetPasswordLinkToken($newResetPasswordLinkToken);
                $customer->sendPasswordResetConfirmationEmail();
            } catch (Exception $exception) {
                $this->_error($exception->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
            }
        
            return $this->_getLocation($customer);
        }else{
            throw Mage::exception('Mage_Core', Mage::helper('customer')->__('No customer account specified.'));
        }
    }

    protected function _retrieve()
    {
        $data["id"] = (int)$this->getRequest()->getParam('id');
        $data["token"] = $this->getRequest()->getParam('token');

        $customer = $this->_validateToken($data);
        
        return $customer->getData();
    }

    protected function _update(array $data)
    {
        $data["id"] = (int)$this->getRequest()->getParam('id');

        $customer = $this->_validateToken($data);

        try {
            $customer->setPassword($data["password"]);
            $customer->setRpToken(null);
            $customer->setRpTokenCreatedAt(null);
//             $customer->cleanPasswordsValidationData();
            $customer->save();
        } catch (Mage_Core_Exception $e) {
            $this->_error($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        } catch (Exception $e) {
            $this->_critical(self::RESOURCE_INTERNAL_ERROR);
        }

        return $customer->getData();
    }

    protected function _validateToken($data)
    {
        if (!is_int($data["id"])
            || !is_string($data["token"])
            || empty($data["token"])
            || empty($data["id"])
            || $data["id"] < 0
        ) {
            throw Mage::exception('Mage_Core', Mage::helper('customer')->__('Invalid password reset token.'));
        }
        
        /** @var $customer Mage_Customer_Model_Customer */
        $customer = Mage::getModel('customer/customer')->load($data["id"]);
        if (!$customer || !$customer->getId()) {
            throw Mage::exception('Mage_Core', Mage::helper('customer')->__('Wrong customer account specified.'));
        }
        
        $customerToken = $customer->getRpToken();
        if (strcmp($customerToken, $data["token"]) != 0 || $customer->isResetPasswordLinkTokenExpired()) {
            throw Mage::exception('Mage_Core', Mage::helper('customer')->__('Your password reset link has expired.'));
        }
        
        return $customer;
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
}
