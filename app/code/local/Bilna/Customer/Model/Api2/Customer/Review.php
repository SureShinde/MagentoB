<?php
/**
 * Description of AddressEdit
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Customer_Model_Api2_Customer_Review extends Mage_Api2_Model_Resource
{
    /**
     * Load customer address by id
     *
     * @param int $id
     * @return Mage_Customer_Model_Address
     */
    protected function _loadCustomerReviewById($id)
    {
        /* @var $address Mage_Customer_Model_Address */
        $address = Mage::getModel('customer/review')->load($id);

        if (!$address->getId()) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        $address->addData($this->_getDefaultAddressesInfo($address));

        return $address;
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
