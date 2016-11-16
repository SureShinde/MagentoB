<?php
class Bilna_Smsverification_Model_Observer extends Mage_Core_Block_Abstract
{
    public function sendSMS($observer) {
        $order = $observer->getOrder();
        $paymentMethod = $order->getPayment()->getMethodInstance()->getTitle();
        if($paymentMethod == Mage::getStoreConfig('payment/cod/title')) {
            $body = Mage::getStoreConfig('bilna/smsverification/template_trans');
            $body = str_replace("[TRX]", $order->getIncrementId(), $body);
            //Send SMS
            $smsHelper = Mage::Helper('smsverification');
            try{
                $msisdn = $smsHelper->validateMobileNumber($order->getShippingAddress()->getTelephone());
                $drObj = Mage::Helper('smsverification')->sendSMS($msisdn,$body);
                $drData = simplexml_load_string($drObj);
                $drID = str_replace("RECEIVED:","",$drData[0]);

                $drModel = Mage::getModel('smsverification/smsdr');
                $data = array('dr' => $drID, 'order_id' => $order->getId(), 'msisdn' => $msisdn, 'created_at' => Mage::getModel('core/date')->date('Y-m-d H:i:s'));
                $drModel->setData($data);
                return $drModel->save();
            } catch (Exception $e) {
                $this->_critical($e->getMessage());
            }
        }
    }
}
