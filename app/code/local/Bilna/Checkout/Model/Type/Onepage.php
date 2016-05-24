<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Checkout
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * One page checkout processing model
 */
class Bilna_Checkout_Model_Type_Onepage extends Mage_Checkout_Model_Type_Onepage
{
    public function getCouponLog()
    {
        return Mage::getModel('bilna_checkout/log');
    }

    /**
     * Create order based on checkout type. Create customer if necessary.
     *
     * @return Mage_Checkout_Model_Type_Onepage
     */
    public function saveOrder()
    {
        $this->_checkActiveCoupon();
        $this->validate();
        $isNewCustomer = false;
        switch ($this->getCheckoutMethod()) {
            case self::METHOD_GUEST:
                $this->_prepareGuestQuote();
                break;
            case self::METHOD_REGISTER:
                $this->_prepareNewCustomerQuote();
                $isNewCustomer = true;
                break;
            default:
                $this->_prepareCustomerQuote();
                break;
        }

        $service = Mage::getModel('sales/service_quote', $this->getQuote());
        $service->submitAll();

        if ($isNewCustomer) {
            try {
                $this->_involveNewCustomer();
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }

        $this->_checkoutSession->setLastQuoteId($this->getQuote()->getId())
            ->setLastSuccessQuoteId($this->getQuote()->getId())
            ->clearHelperData();

        $order = $service->getOrder();
        if ($order) {
            Mage::dispatchEvent('checkout_type_onepage_save_order_after',
                array('order'=>$order, 'quote'=>$this->getQuote()));

            /**
             * a flag to set that there will be redirect to third party after confirmation
             * eg: paypal standard ipn
             */
            $redirectUrl = $this->getQuote()->getPayment()->getOrderPlaceRedirectUrl();
            /**
             * we only want to send to customer about new order when there is no redirect to third party
             */
            if (!$redirectUrl && $order->getCanSendNewEmailFlag()) {
                try {
                    $order->sendNewOrderEmail();
                } catch (Exception $e) {
                    Mage::logException($e);
                }
            }

            // add order information to the session
            $this->_checkoutSession->setLastOrderId($order->getId())
                ->setRedirectUrl($redirectUrl)
                ->setLastRealOrderId($order->getIncrementId());

            // as well a billing agreement can be created
            $agreement = $order->getPayment()->getBillingAgreement();
            if ($agreement) {
                $this->_checkoutSession->setLastBillingAgreementId($agreement->getId());
            }
        }

        // add recurring profiles information to the session
        $profiles = $service->getRecurringPaymentProfiles();
        if ($profiles) {
            $ids = array();
            foreach ($profiles as $profile) {
                $ids[] = $profile->getId();
            }
            $this->_checkoutSession->setLastRecurringProfileIds($ids);
            // TODO: send recurring profile emails
        }

        Mage::dispatchEvent(
            'checkout_submit_all_after',
            array('order' => $order, 'quote' => $this->getQuote(), 'recurring_profiles' => $profiles)
        );

        return $this;
    }

    /**
     * This function will prevent raising condition if different user with same unique coupon code
     * checkout at the same time (prevent single use coupon to be used multiple times at once)
     *
     */
    private function _checkActiveCoupon()
    {
        $couponCode = $this->getQuote()->getCouponCode();
        if (!is_null($couponCode)) {
            $couponData = $this->_getCouponData($couponCode);

            if ($couponData['type'] == 1) { // if this coupon is unique coupon
                $this->_deleteOlderCouponLog(); // delete all active coupon logged after one minute or older
                
                $couponLogData = array(
                    "coupon_code" => $couponCode,
                    "quote_id" => $this->getQuote()->getId()
                );

                $uniqueCouponLog = $this->getCouponLog();
                $uniqueCouponLog->setData($couponLogData);

                try {
                    $uniqueCouponLog->save();
                } catch (Exception $e) {
                    $errorMessage = $e->getMessage();

                    // This is how we prevent raising condition by utilizing database unique lock
                    if ($errorMessage == "SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry '".$couponCode."' for key 'coupon_code'") {
                        $appliedRuleIds = $this->getQuote()->getAppliedRuleIds();
                        $appliedRuleIdsArray = explode(',', $appliedRuleIds);

                        if (count($appliedRuleIdsArray) > 1) {
                            foreach ($appliedRuleIdsArray as $key => $ruleId) {
                                if ($ruleId == $couponData['rule_id']) {
                                    unset($appliedRuleIdsArray[$key]);
                                }
                            }

                            $appliedRuleIds = implode(',', $appliedRuleIdsArray);
                        } else {
                            $appliedRuleIds = null;
                        }

                        $quoteData = Mage::getModel('sales/quote')->load($this->getQuote()->getId());
                        $quoteData->setCouponCode(null);
                        $quoteData->setAppliedRuleIds($appliedRuleIds);
                        $quoteData->save();
                        $this->getQuote()->setCouponCode(null);
                        $this->getQuote()->setAppliesRuleIds($appliedRuleIds);
                    }
                }
            }
        }
    }

    private function _getCouponData ($couponCode)
    {
        $couponCollection = Mage::getModel('salesrule/coupon')
            ->getCollection()
            ->addFieldToFilter('code', $couponCode)
            ->addFieldToFilter('type', 1)
            ->getFirstItem()
            ->getData();

        return $couponCollection;
    }

    private function _deleteOlderCouponLog()
    {
        $uniqueCouponLog = $this->getCouponLog();
        $time = time();
        $oneMinute = Mage::getModel('core/date')->date('Y-m-d H:i:s', ($time - 60));
        $olderCouponLogs = Mage::getModel('bilna_checkout/log')
            ->getCollection()
            ->addFieldToFilter('created_at', array('lteq' => 'NOW() - INTERVAL 1 MINUTE'))
            ->getColumnValues('id');

        if (count($olderCouponLogs) > 0) {
            foreach ($olderCouponLogs as $olderCouponLog) {
                $uniqueCouponLog->setId($olderCouponLog)->delete();
            }
        }
    }
}
