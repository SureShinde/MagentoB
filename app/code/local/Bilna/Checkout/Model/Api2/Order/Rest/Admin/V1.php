<?php

/**
 * API2 class for coupon (admin)
 *
 * @category   Bilna
 * @package    Bilna_Checkout
 * @author     Development Team <development@bilna.com>
 */
class Bilna_Checkout_Model_Api2_Order_Rest_Admin_V1 extends Bilna_Checkout_Model_Api2_Order_Rest
{
	/**
     * Covert Quote to Order
     *
     * @param  $quoteId
     * @param  $store
     * @return bool
     */
    protected function _create(array $data)
    {
        $quoteId = $data['entity_id'];
        $storeId = isset($data['store_id']) ? $data['store_id'] : 1;

        try {

        	$quote = $this->_getQuote($quoteId, $store);
        	if ($quote->getIsMultiShipping()) {
        		throw Mage::throwException('Invalid Checkout Type');
        	}
        	if ($quote->getCheckoutMethod() == Mage_Checkout_Model_Api_Resource_Customer::MODE_GUEST
                && !Mage::helper('checkout')->isAllowedGuestCheckout($quote, $quote->getStoreId())) {
        		throw Mage::throwException('Guest Checkout is not Enable');
        	}

        	 /** @var $customerResource Mage_Checkout_Model_Api_Resource_Customer */
	        $customerResource = Mage::getModel("checkout/api_resource_customer");
	        $isNewCustomer = $customerResource->prepareCustomerForQuote($quote);

	        $quote->collectTotals();
            /** @var $service Mage_Sales_Model_Service_Quote */
            $service = Mage::getModel('sales/service_quote', $quote);
            $service->submitAll();

            if ($isNewCustomer) {
                try {
                    $customerResource->involveNewCustomer($quote);
                } catch (Exception $e) {
                    Mage::logException($e);
                }
            }

            $order = $service->getOrder();
            if ($order) {
                Mage::dispatchEvent('checkout_type_onepage_save_order_after',
                    array('order' => $order, 'quote' => $quote));

                try {
                    $order->sendNewOrderEmail();
                } catch (Exception $e) {
                    Mage::logException($e);
                }
            }

            Mage::dispatchEvent(
                'checkout_submit_all_after',
                array('order' => $order, 'quote' => $quote)
            );
        } catch (Mage_Core_Exception $e) {
            $this->_critical($e->getMessage());
        }

        return $this->_getLocation($order);

    }
}