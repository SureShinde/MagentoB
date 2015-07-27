<?php

/**
 * API2 class for payment (admin)
 *
 * @category   Bilna
 * @package    Bilna_Checkout
 * @author     Development Team <development@bilna.com>
 */
class Bilna_Checkout_Model_Api2_Payment_Rest_Admin_V1 extends Bilna_Checkout_Model_Api2_Shipping_Rest
{
	/**
     * Set an Shipping Method for Shopping Cart
     *
     * @param  $quoteId
     * @param  $shippingMethod
     * @param  $store
     * @return bool
     */
    protected function _retrieve()
    {
        $quoteId = $this->getRequest()->getParam('id');
        $storeId = 1;

        try {
            $quote = $this->_getQuote($quoteId, $storeId);
            $store = $quote->getStoreId();

            $total = $quote->getBaseSubtotal();

            $methodsResult = array();
            $methods = Mage::helper('payment')->getStoreMethods($store, $quote);

            foreach ($methods as $method)
            {
                /** @var $method Mage_Payment_Model_Method_Abstract */
                if ($this->_canUsePaymentMethod($method, $quote)) {
                    $isRecurring = $quote->hasRecurringItems() && $method->canManageRecurringProfiles();

                    if ($total != 0 || $method->getCode() == 'free' || $isRecurring) {
                        $methodsResult[] = array(
                            'code' => $method->getCode(),
                            'title' => $method->getTitle(),
                            'cc_types' => $this->_getPaymentMethodAvailableCcTypes($method),
                        );
                    }
                }
            }

        } catch (Mage_Core_Exception $e) {
            $this->_error($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        }

        return array("payment_methods" => $methodsResult);
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

    protected function _getPaymentMethodAvailableCcTypes($method)
    {
        $ccTypes = Mage::getSingleton('payment/config')->getCcTypes();
        $methodCcTypes = explode(',', $method->getConfigData('cctypes'));
        foreach ($ccTypes as $code => $title) {
            if (!in_array($code, $methodCcTypes)) {
                unset($ccTypes[$code]);
            }
        }
        if (empty($ccTypes)) {
            return null;
        }

        return $ccTypes;
    }
}