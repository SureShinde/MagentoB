<?php
/**
 * Description of Bilna_Customer_Model_Api2_Wishlistcollectiongetcover
 *
 * @author Bilna Development Team <development@bilna.com>
 */
class Bilna_Customer_Model_Api2_Wishlistcollectiongetcover extends Mage_Api2_Model_Resource
{
    public function _construct()
    {
        Mage::app()->getStore()->setStoreId(self::DEFAULT_STORE_ID);
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
        /* @var $customer Mage_Customer_Model_Customer */
        
        $customer = Mage::getModel('customer/customer')->load($id);
        
        if (!$customer->getId()) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        
        return $customer;
    }
}
