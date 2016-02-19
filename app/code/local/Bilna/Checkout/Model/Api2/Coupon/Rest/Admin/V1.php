<?php

/**
 * API2 class for coupon (admin)
 *
 * @category   Bilna
 * @package    Bilna_Checkout
 * @author     Development Team <development@bilna.com>
 */
class Bilna_Checkout_Model_Api2_Coupon_Rest_Admin_V1 extends Bilna_Checkout_Model_Api2_Customer_Rest
{
	/**
     * Set an Coupon for Shopping Cart
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
        $couponCode =  $data['coupon_code'];

        try {

            $quote = $this->_applyCoupon($quoteId, $couponCode, $storeId);

        } catch (Mage_Core_Exception $e) {
            $this->_critical($e->getMessage());
        }

        return $this->_getLocation($quote);

    }

    /**
     * Delete/Remove coupon
     *
     * @param array $data
     * @throws Mage_Api2_Exception
     */
    protected function _delete()
    {
    	$quoteId = $this->getRequest()->getParam('id');
        $storeId = 1;

        try {
        	$couponCode = '';
            $this->_applyCoupon($quoteId, $couponCode, $storeId);

        } catch (Mage_Core_Exception $e) {
            $this->_critical($e->getMessage());
        }
    }

    /**
     * @param  $quoteId
     * @param  $couponCode
     * @param  $store
     * @return bool
     */
    protected function _applyCoupon($quoteId, $couponCode, $store = null)
    {
        $quote = $this->_getQuote($quoteId, $store);

        if (!$quote->getItemsCount()) {
            throw Mage::throwException('Quote is Empty');
        }

        $oldCouponCode = $quote->getCouponCode();
        if (!strlen($couponCode) && !strlen($oldCouponCode)) {
            return false;
        }

        try {
            $quote->getShippingAddress()->setCollectShippingRates(true);
            $quote->setCouponCode(strlen($couponCode) ? $couponCode : '')
                ->collectTotals()
                ->save();
        } catch (Exception $e) {
            throw Mage::throwException("cannot_apply_coupon_code");
        }

        if ($couponCode) {
            if (!$couponCode == $quote->getCouponCode()) {
                throw Mage::throwException('coupon_code_is_not_valid');
            }
        }

        return $quote;
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
}