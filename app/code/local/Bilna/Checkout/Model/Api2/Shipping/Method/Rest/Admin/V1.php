<?php

/**
 * API2 class for shipping method(admin)
 *
 * @category   Bilna
 * @package    Bilna_Checkout
 * @author     Development Team <development@bilna.com>
 */
class Bilna_Checkout_Model_Api2_Shipping_Method_Rest_Admin_V1 extends Bilna_Checkout_Model_Api2_Shipping_Method_Rest
{
	/**
     * Set an Shipping Method for Shopping Cart
     *
     * @param  $quoteId
     * @param  $shippingMethod
     * @param  $store
     * @return bool
     */
    protected function _create(array $data)
    {
    	$quoteId = $data['entity_id'];
        $storeId = isset($data['store_id']) ? $data['store_id'] : 1;
        $shippingMethod =  $data['shipping_method'];

        try {
            $quote = $this->_getQuote($quoteId, $storeId);

            $quoteShippingAddress = $quote->getShippingAddress();
            if (is_null($quoteShippingAddress->getId())) {
                throw Mage::throwException('Shipping Address is not set');
            }

            $rate = $quote->getShippingAddress()->collectShippingRates()->getShippingRateByCode($shippingMethod);

            if (!$rate) {
                throw Mage::throwException('Shipping Method is not Available');
            }

            $quote->getShippingAddress()->setShippingMethod($shippingMethod);
            $quote->collectTotals()->save();

        } catch (Mage_Core_Exception $e) {
            $this->_error($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        }

        return $this->_getLocation($quote);
    }

    protected function _retrieve()
    {
        $quoteId = $this->getRequest()->getParam('id');
        $quote = $this->__getCollection($quoteId);

        $quoteDataRaw = $quote->getData();
        
        if(empty($quoteDataRaw)){
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }

        $quoteData = $quoteDataRaw[0];
        $addresses = $this->_getAddresses(array($quoteData['entity_id']));
        $items     = $this->_getItems(array($quoteData['entity_id']));

        if ($addresses) {
            $quoteData['addresses'] = $addresses[$quoteData['entity_id']];
        }
        if ($items) {
            $quoteData['quote_items'] = $items[$quoteData['entity_id']];
        }
        
        return $quoteData;
    }

    /**
     * Get orders list
     *
     * @return array
     */
    /*protected function _retrieveCollection()
    {
    	die('yyyyyyyyyyyy');
    }*/
}