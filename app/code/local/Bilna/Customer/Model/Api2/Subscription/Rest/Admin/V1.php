<?php

class Bilna_Customer_Model_Api2_Subscription_Rest_Admin_V1 extends Bilna_Customer_Model_Api2_Subscription_Rest
{


    protected function _delete()
    {
        $subscriber = Mage::getModel('newsletter/subscriber')->load($this->getRequest()->getParam('id'));
        
        if($subscriber->getCustomerId()){
            $customer = Mage::getModel('customer/customer')->load($subscriber->getCustomerId());
            if($customer){
                $customer->setIsSubscribed(FALSE);
            
                try {
                    $customer->save();
                } catch (Mage_Core_Exception $e) {
                    $this->_error($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
                } catch (Exception $e) {
                    $this->_critical(self::RESOURCE_INTERNAL_ERROR);
                }
            }
        }
        
        $subscriber->unsubscribe();
    }
}