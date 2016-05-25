<?php
/**
 * Description of Bilna_Checkout_Helper_Data
 *
 * @author Bilna Development Team <development@bilna.com>
 */

/**
 * One page checkout processing model
 */
class Bilna_Checkout_Model_Type_Onepage extends Mage_Checkout_Model_Type_Onepage
{
    public function getCouponLog()
    {
        return Mage::getModel('bilna_checkout/activeCoupon');
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
     * This function will prevent racing condition if different user with same unique coupon code
     * checkout at the same time (prevent single use coupon to be used multiple times at once)
     */
    private function _checkActiveCoupon()
    {
        $couponCode = $this->getQuote()->getCouponCode();
        if (is_null($couponCode)) {
            return;
        }

        $couponData = $this->_getCouponData($couponCode)->getData();

        if (count($couponData) == 0) {
            return;
        }

        if ($couponData['type'] != 1) { // only check for unique coupon code
            return;
        }

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

            // This is how we prevent racing condition by utilizing database unique lock
            if ($errorMessage == "SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry '".$couponCode."' for key 'coupon_code'") {
                Mage::throwException(Mage::helper('checkout')->__('Kupon yang anda gunakan sudah pernah terpakai.'));
            } else {
                Mage::logException($errorMessage);
            }
        }
    }

    private function _getCouponData ($couponCode)
    {
        $couponData = Mage::getModel('salesrule/coupon')->load($couponCode, 'code');
        return $couponData;
    }

    private function _deleteOlderCouponLog()
    {
        $uniqueCouponLog = $this->getCouponLog();
        $time = time();
        $oneMinute = Mage::getModel('core/date')->date('Y-m-d H:i:s', ($time - 60));
        $olderCouponLogs = $this->getCouponLog()
            ->getCollection()
            ->addFieldToFilter('created_at', array('lteq' => new Zend_Db_Expr("NOW() - INTERVAL 1 MINUTE")))
            ->getColumnValues('id');

        if (count($olderCouponLogs) > 0) {
            $olderCouponIds = implode(",", $olderCouponLogs);
            $sql = "DELETE FROM bilna_unique_coupon_log WHERE id IN (".$olderCouponIds.")";
            $connectionDelete = Mage::getSingleton('core/resource')->getConnection('core_write');
            $connectionDelete->query($sql);
        }
    }
}
