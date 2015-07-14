<?php

/**
 * API2 class for checkout cart products (admin)
 *
 * @category   Bilna
 * @package    Bilna_Checkout
 * @author     Development Team <development@bilna.com>
 */

class Bilna_Checkout_Model_Api2_Product extends Bilna_Checkout_Model_Api2_Resource
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

    /**
     * Return loaded product instance
     *
     * @param  int|string $productId (SKU or ID)
     * @param  int|string $store
     * @param  string $identifierType
     * @return Mage_Catalog_Model_Product
     */
    protected function _getProduct($productId, $storeId = 1, $identifierType = null)
    {
        $product = Mage::helper('catalog/product')->getProduct($productId,
                        $storeId,
                        $identifierType
        );
        return $product;
    }

    /**
     * Get request for product add to cart procedure
     *
     * @param   mixed $requestInfo
     * @return  Varien_Object
     */
    protected function _getProductRequest($requestInfo)
    {
        if ($requestInfo instanceof Varien_Object) {
            $request = $requestInfo;
        } elseif (is_numeric($requestInfo)) {
            $request = new Varien_Object();
            $request->setQty($requestInfo);
        } else {
            $request = new Varien_Object($requestInfo);
        }

        if (!$request->hasQty()) {
            $request->setQty(1);
        }
        return $request;
    }

    //_getQuoteItemByProduct
    /**
     * Get QuoteItem by Product and request info
     *
     * @param Mage_Sales_Model_Quote $quote
     * @param Mage_Catalog_Model_Product $product
     * @param Varien_Object $requestInfo
     * @return Mage_Sales_Model_Quote_Item
     * @throw Mage_Core_Exception
     */
    protected function _getQuoteItemByProduct(Mage_Sales_Model_Quote $quote,
                            Mage_Catalog_Model_Product $product,
                            Varien_Object $requestInfo)
    {
        $cartCandidates = $product->getTypeInstance(true)
                        ->prepareForCartAdvanced($requestInfo,
                                $product,
                                Mage_Catalog_Model_Product_Type_Abstract::PROCESS_MODE_FULL
        );

        /**
         * Error message
         */
        if (is_string($cartCandidates)) {
            throw Mage::throwException($cartCandidates);
        }

        /**
         * If prepare process return one object
         */
        if (!is_array($cartCandidates)) {
            $cartCandidates = array($cartCandidates);
        }

        /** @var $item Mage_Sales_Model_Quote_Item */
        $item = null;
        foreach ($cartCandidates as $candidate) {
            if ($candidate->getParentProductId()) {
                continue;
            }

            $item = $quote->getItemByProduct($candidate);
        }

        if (is_null($item)) {
            $item = Mage::getModel("sales/quote_item");
        }

        return $item;
    }
}
