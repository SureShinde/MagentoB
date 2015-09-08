<?php

/**
 * API2 class for paymethod (admin)
 *
 * @category   Bilna
 * @package    Bilna_Paymethod
 * @author     Development Team <development@bilna.com>
 */
class Bilna_Paymethod_Model_Api2_Klikpay_Rest_Admin_V1 extends Bilna_Paymethod_Model_Api2_Klikpay_Rest
{
	protected function _retrieve()
    {
    	$orderIncrementId = $this->getRequest()->getParam('id');
    	$order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);

    	$klikpayUserId = Mage::getStoreConfig('payment/klikpay/klikpay_user_id');
    	$transactionNo = $orderIncrementId;
    	$currency = "IDR";
    	$transactionAmount = number_format((int) $order->getGrandTotal(), 2, null, '');
    	$payType = $order->getPayType();
    	$callBackUrl = Mage::getBaseUrl() . "success/" . $transactionNo;
    	$transactionDate = date('d/m/Y H:i:s', strtotime($order->getCreatedAt()));
    	$clearKey = Mage::getStoreConfig('payment/klikpay/klikpay_clearkey');
    	$signature = Mage::helper('paymethod/klikpay')->signature($klikpayUserId, $transactionNo, $currency, $clearKey, $transactionDate, $transactionAmount);

    	$data = array (
            'klikPayCode' => $klikpayUserId,
            'transactionNo' => $transactionNo,
            'totalAmount' => $transactionAmount,
            'currency' => $currency,
            'payType' => $payType,
            'callback' => $callBackUrl,
            'transactionDate' => $transactionDate,
            'descp' => '',
            'miscFee' => '',
            'signature' => abs($signature)
        );

        $order->setKlikpaySignature(abs($signature))->save();

        return array('data' => $data);
    }

}