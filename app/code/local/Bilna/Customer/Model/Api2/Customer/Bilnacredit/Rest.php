<?php
/**
 * Description of Rest
 *
 * @author Bilna Development Team <development@bilna.com>
 */
abstract class Bilna_Customer_Model_Api2_Customer_Bilnacredit_Rest extends Bilna_Customer_Model_Api2_Customer_Bilnacredit
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
        return $customerId;
    }
}
