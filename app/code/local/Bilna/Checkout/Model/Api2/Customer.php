<?php

/**
 * API2 class for checkout cart customer (admin)
 *
 * @category   Bilna
 * @package    Bilna_Checkout
 * @author     Development Team <development@bilna.com>
 */

class Bilna_Checkout_Model_Api2_Customer extends Mage_Api2_Model_Resource
{
	/**
     *
     */
    protected function _getCustomer($customerId)
    {
        /** @var $customer Mage_Customer_Model_Customer */
        $customer = Mage::getModel('customer/customer')
            ->load($customerId);
        if (!$customer->getId()) {
            throw Mage::throwException('Customer Not Exists');
        }

        return $customer;
    }
}