<?php
class Bilna_Smsverification_Model_Observer extends Mage_Core_Block_Abstract
{
    public function sendSMS($observer) {
        $order = $observer->getOrder();
        $paymentMethod = $order->getPayment()->getMethodInstance()->getTitle();
        if($paymentMethod == Mage::getStoreConfig('payment/cod/title')) {
            $body = Mage::getStoreConfig('bilna/smsverification/template_trans');
            $body = str_replace("[TRX]", $order->getId(), $body);
            //Send SMS
            $smsHelper = Mage::Helper('smsverification');
            try{
                $msisdn = $smsHelper->validateMobileNumber($order->getShippingAddress()->getTelephone());
                $drObj = Mage::Helper('smsverification')->sendSMS($msisdn,$body);
            } catch (Exception $e) {
                $this->_critical($e->getMessage());
            }

        }
    }
}
