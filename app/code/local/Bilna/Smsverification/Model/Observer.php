<?php
class Bilna_Smsverification_Model_Observer extends Mage_Core_Block_Abstract
{
    public function sendSMS($observer) {
        $isEnabledVerification = Mage::getStoreConfig('bilna/smsverification/voucher_check');
        if (!$isEnabledVerification) {
            return;
        }
        $order = $observer->getOrder();

        $isCod = Mage::Helper('cod')->isCodOrder($order);
        if(!$isCod) return;

        $body = Mage::getStoreConfig('bilna/smsverification/template_trans');
        $body = str_replace("[TRX]", $order->getIncrementId(), $body);

        $smsHelper = Mage::Helper('smsverification');
        if ($smsHelper->isEnabledValidate()) {
            try{
                $msisdn = $smsHelper->validateMobileNumber($order->getShippingAddress()->getTelephone());
                $code = $smsHelper->sendSMS($msisdn,$body);
                $data = array('code' => $code, 'order_id' => $order->getId(), 'msisdn' => $msisdn);
                Mage::getModel('smsverification/smsdr')->setData($data)->save();
            } catch (Exception $e) {
                Mage::log("Failed to send SMS for COD, Order Number: ".$order->getIncrementId().", Reason: ".$e,Zend_Log::ERR);
            }
        }
    }
}
