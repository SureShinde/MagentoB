<?php

/**
 * API2 class for coupon (admin)
 *
 * @category   Bilna
 * @package    Bilna_Checkout
 * @author     Development Team <development@bilna.com>
 */

use Pheanstalk\Pheanstalk;

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
        $tokenId = isset($data['token_id']) ? $data['token_id'] : '';
        $payment = isset($data['payment']) ? $data['payment'] : '';

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

            if(!empty($payment))
                $quote->getPayment()->importData($payment);

	        $quote->collectTotals();
            /* checking customer using their poinst or not*/
            if($payment['use_points'])
            {
                $this->pointsCheck($quote, $payment);
            }
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

            if($payment['use_points'])
            {
                $order = $this->submitPoints($order, $payment);
                $order->save();
            }

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

            $lastOrderId = $order->getId();
            $paymentCode = $order->getPayment()->getMethodInstance()->getCode();

            if (in_array($paymentCode, $this->getPaymentMethodCc()))
            {
                $charge = Mage::getModel('paymethod/api')->creditcardCharge($order, $tokenId);
                $setData = array(
                    'order_id'      => $lastOrderId,
                    'increment_id'  => $charge->order_id,
                    'gross_amount'  => $charge->gross_amount,
                    'payment_type'  => $charge->payment_type,
                    'bank'          => $charge->bank,
                    'token_id'      => $tokenId,
                    'status_code'   => $charge->status_code,
                    'status_message'=> $charge->status_message,
                    'transaction_id'=> $charge->transaction_id,
                    'masked_card'   => $charge->masked_card,
                    'transaction_time'=> $charge->transaction_time,
                    'transaction_status'=> $charge->transaction_status,
                    'fraud_status' => $charge->fraud_status,
                    'approval_code' => $charge->approval_code,
                    'created_at'    => date('Y-m-d H:i:s')
                );

                Mage::getModel('paymethod/veritrans')
                    ->setData($setData)
                    ->addData()
                    ->save();

                $pheanstalk = new Pheanstalk('127.0.0.1');
                $pheanstalk
                  ->useTube('invoice')
                  ->put(json_encode($setData));

                //Mage::getModel('paymethod/vtdirect')->updateOrder($order, $paymentCode, $charge);
                //Mage::register('response_charge', $charge);
                Mage::dispatchEvent('sales_order_place_after', array ('order' => $order));

            }

        } catch (Mage_Core_Exception $e) {
            $this->_critical($e->getMessage());
        }

        return $this->_getLocation($order);

    }

    protected function submitPoints($order, $payment)
    {
        if ($order->getCustomerIsGuest()) 
        {
            return $order;
        }

        if ($order->getCustomerId())
        {
            $quote = $order->getQuote();
            if (!$quote instanceof Mage_Sales_Model_Quote) {
                $quote = Mage::getModel('sales/quote')
                        ->setSharedStoreIds(array($order->getStoreId()))
                        ->load($order->getQuoteId());
            }
            $sum = floatval($quote->getData('base_subtotal_with_discount'));
            $limitedPoints = Mage::helper('points')->getLimitedPoints($sum, $order->getCustomer(), $order->getStoreId());

            $pointsAmount = (int) $payment['points_amount'];
            $customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
            $customerPoints = Mage::getModel('points/summary')->loadByCustomer($customer)->getPoints();

            if (
                    $customerPoints < $pointsAmount ||
                    $limitedPoints < $pointsAmount ||
                    !Mage::helper('points')->isAvailableToRedeem($pointsAmount)
            ) {
                Mage::throwException('Incorrect points amount');
            }

            $amountToSubtract = -$pointsAmount;
            $moneyForPointsBase = Mage::getModel('points/api')->changePointsToMoney($amountToSubtract, $customer, $order->getStore()->getWebsite());
            $moneyForPoints = $order->getBaseCurrency()->convert($moneyForPointsBase, $order->getOrderCurrencyCode());

            $order->setGrandTotal($order->getGrandTotal() + $moneyForPoints);
            $order->setBaseGrandTotal($order->getBaseGrandTotal() + $moneyForPointsBase );            
            $order->setAmountToSubtract($amountToSubtract);
            $order->setBaseMoneyForPoints($moneyForPointsBase);
            $order->setMoneyForPoints($moneyForPoints);
        }

        return $order;
    }

    protected function pointsCheck($quote, $payment)
    {
        if ($quote->getCustomerIsGuest()) 
        {
            return $quote;
        }

        if ($quote->getCustomerId())
        {
            if (!$quote instanceof Mage_Sales_Model_Quote) {
                $quote = Mage::getModel('sales/quote')
                        ->setSharedStoreIds(array($quote->getStoreId()))
                        ->load($quote->getId());
            }
            $sum = floatval($quote->getData('base_subtotal_with_discount'));
            $limitedPoints = Mage::helper('points')->getLimitedPoints($sum, $quote->getCustomer(), $quote->getStoreId());

            $pointsAmount = (int) $payment['points_amount'];
            $customer = Mage::getModel('customer/customer')->load($quote->getCustomerId());
            $customerPoints = Mage::getModel('points/summary')->loadByCustomer($customer)->getPoints();

            if (
                    $customerPoints < $pointsAmount ||
                    $limitedPoints < $pointsAmount ||
                    !Mage::helper('points')->isAvailableToRedeem($pointsAmount)
            ) {
                Mage::throwException('Incorrect points amount');
            }

        }

        return $quote;
    }

    protected function getPaymentMethodCc()
    {
        return Mage::helper('paymethod')->getPaymentMethodCc();
    }
}