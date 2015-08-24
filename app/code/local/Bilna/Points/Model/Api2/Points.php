<?php

/**
 * points api resource
 *
 * @category   Bilna
 * @package    Bilna_Points
 * @author     Bilna Development Team <core@magentocommerce.com>
 */
class Bilna_Points_Model_Api2_Points extends Mage_Api2_Model_Resource
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