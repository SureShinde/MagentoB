<?php

/**
 * API2 class for rest shipping method(admin)
 *
 * @category   Bilna
 * @package    Bilna_Checkout
 * @author     Development Team <development@bilna.com>
 */
abstract class Bilna_Checkout_Model_Api2_Shipping_Method_Rest extends Bilna_Checkout_Model_Api2_Shipping_Method
{
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

        $quote->setStoreId($storeId)
                    ->load($quoteId);
                    
        if (is_null($quote->getId())) {
            Mage::throwException("Quote Not Exists");
        }

        return $quote;
    }
}