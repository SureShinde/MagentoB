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
        //Send SMS
        $smsHelper = Mage::Helper('smsverification');
        try{
            $msisdn = $smsHelper->validateMobileNumber($order->getShippingAddress()->getTelephone());
            $drID = $smsHelper->sendSMS($msisdn,$body);
            $drModel = Mage::getModel('smsverification/smsdr');
            $data = array('code' => $drID, 'order_id' => $order->getId(), 'msisdn' => $msisdn);
            $drModel->setData($data);
            return $drModel->save();
        } catch (Exception $e) {
            throw new Exception($e->getMessage());

        }
    }
}
