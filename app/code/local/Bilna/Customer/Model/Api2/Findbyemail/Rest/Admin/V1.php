<?php

class Bilna_Customer_Model_Api2_Findbyemail_Rest_Admin_V1 extends Bilna_Customer_Model_Api2_Findbyemail_Rest
{
    protected function _retrieve()
    {
        $email = $this->getRequest()->getParam('email');
        
        if(!$email) {
            return $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        
        $customer = Mage::getModel('customer/customer'); 
        $customer->setWebsiteId(1); 
        $customer->loadByEmail($email);
        
        if(!$customer->getId()) {
            return $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        
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

        return $result;
    }
}