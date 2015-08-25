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

    /**
     * Retrieves quote by quote identifier and store code or by quote identifier
     *
     * @param int $quoteId
     * @param string|int $store
     * @return Mage_Sales_Model_Quote
     */
    protected function _getQuote($quoteId, $storeId = 1)
    {
        /** @var $quote Mage_Sales_Model_Quote */
        $quote = Mage::getModel("sales/quote");

        if (!(is_string($storeId) || is_integer($storeId))) {
            $quote->loadByIdWithoutStore($quoteId);
        } else {
            $quote->setStoreId($storeId)
                    ->load($quoteId);
        }
        if (is_null($quote->getId())) {
            Mage::throwException("Quote Not Exists");
        }

        return $quote;
    }
}