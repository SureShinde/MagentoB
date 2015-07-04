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
         *
         * @var $log Mage_Log_Model_Customer
         */
        $log = Mage::getModel('log/customer');
        $log->loadByCustomer($this->getRequest()
            ->getParam('id'));
        
        $data = parent::_retrieve();
        $data['is_confirmed'] = (int) ! (isset($data['confirmation']) && $data['confirmation']);
        
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
         *
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
}
