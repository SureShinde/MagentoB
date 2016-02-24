<?php

/**
 * API2 class for payment method(admin)
 *
 * @category   Bilna
 * @package    Bilna_Checkout
 * @author     Development Team <development@bilna.com>
 */
class Bilna_Checkout_Model_Api2_Payment_Method_Rest_Admin_V1 extends Bilna_Checkout_Model_Api2_Shipping_Method_Rest {
    protected function _preparePaymentData($data) {
        if (!(is_array($data) && is_null($data[0]))) {
            return array();
        }

        return $data;
    }

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
        $paymentMethod =  $data['payment_method'];

        try {
            $quote = $this->_getQuote($quoteId, $storeId);

            $paymentData = $this->_preparePaymentData($paymentMethod);

            if (empty($paymentData)) {
                throw Mage::throwException("Payment Method Empty");
            }

            if ($quote->isVirtual()) {
                // check if billing address is set
                if (is_null($quote->getBillingAddress()->getId())) {
                    throw Mage::throwException('billing_address_is_not_set');
                }
                $quote->getBillingAddress()->setPaymentMethod(
                    isset($paymentData['method']) ? $paymentData['method'] : null
                );
            } else {
                // check if shipping address is set
                if (is_null($quote->getShippingAddress()->getId())) {
                    throw Mage::throwException('shipping_address_is_not_set');
                }
                $quote->getShippingAddress()->setPaymentMethod(
                    isset($paymentData['method']) ? $paymentData['method'] : null
                );
            }

            if (!$quote->isVirtual() && $quote->getShippingAddress()) {
                $quote->getShippingAddress()->setCollectShippingRates(true);
            }

            $total = $quote->getBaseSubtotal();
            $store = $this->_getStore();
            $methods = Mage::helper('payment')->getStoreMethods($store, $quote);
            foreach ($methods as $method) {
                if ($method->getCode() == $paymentData['method']) {
                    /** @var $method Mage_Payment_Model_Method_Abstract */
                    if (!($this->_canUsePaymentMethod($method, $quote)
                        && ($total != 0
                            || $method->getCode() == 'free'
                            || ($quote->hasRecurringItems() && $method->canManageRecurringProfiles())))
                    ) {
                        throw Mage::throwException("method_not_allowed");
                    }
                }
            }

            $payment = $quote->getPayment();
            $payment->importData($paymentData);


            $quote->setTotalsCollectedFlag(false)
                ->collectTotals()
                ->save();

        } catch (Mage_Core_Exception $e) {
            $this->_error($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        }

        return $this->_getLocation($quote);
    }

    /**
     * @param  $method
     * @param  $quote
     * @return bool
     */
    protected function _canUsePaymentMethod($method, $quote)
    {
        if (!($method->isGateway() || $method->canUseInternal())) {
            return false;
        }

        if (!$method->canUseForCountry($quote->getBillingAddress()->getCountry())) {
            return false;
        }

        if (!$method->canUseForCurrency(Mage::app()->getStore($quote->getStoreId())->getBaseCurrencyCode())) {
            return false;
        }

        /**
         * Checking for min/max order total for assigned payment method
         */
        $total = $quote->getBaseGrandTotal();
        $minTotal = $method->getConfigData('min_order_total');
        $maxTotal = $method->getConfigData('max_order_total');

        if ((!empty($minTotal) && ($total < $minTotal)) || (!empty($maxTotal) && ($total > $maxTotal))) {
            return false;
        }

        return true;
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
