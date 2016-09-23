<?php
/**
 * Description of Bilna_Sales_Model_Api2_Order_Info_Rest
 *
 * @author  Bilna Development Team <development@bilna.com>
 * @path    app/code/local/Bilna/Sales/Model/Api2/Order/Info/Rest.php
 */

abstract class Bilna_Sales_Model_Api2_Order_Info_Rest extends Bilna_Sales_Model_Api2_Order_Info {
    protected $_order;
    protected $_helper;
    
    protected function _getOrder($orderId) {
        return Mage::getModel('sales/order')->load($orderId);
    }
    
    protected function _getOrderStatus($order) {
        $statusCode = $order->getStatus();
        $status = Mage::getModel('sales/order_status')->load($statusCode);
        
        return [
            'code' => $statusCode,
            'label' => $status->getLabel(),
        ];
    }
    
    protected function _isCanceled($orderState) {
        return ($orderState === Mage_Sales_Model_Order::STATE_CANCELED);
    }

    protected function _getPaymentInfo($paymentMethod) {
        return '';
    }

    protected function _isCreditCard($helper, $paymentMethod) {
        if ($helper) {
            if (in_array($paymentMethod, $helper->getPaymentMethodCc())) {
                return true;
            }
        }
        
        return false;
    }
    
    protected function _isRedirect($helper, $paymentMethod) {
        $klikpay = $helper->getPaymentMethodKlikpay();
        $vtdirect = $helper->getPaymentMethodVtdirect();
        $method = array_merge($klikpay, $vtdirect);

        if (in_array($paymentMethod, $method)) {
            return true;
        }
        
        return false;
    }
    
    protected function _getAdditionalInfo($helper, $order, $paymentMethod) {
        $result = [];
        
        if (in_array($paymentMethod, $helper->getPaymentMethodKlikpay())) {
            $additionalInfo = $this->_getAdditionalInfoKlikpay($order);
            $result['data'] = $additionalInfo['data'];
            $result['action'] = $additionalInfo['action']; 
        }
        elseif (in_array($paymentMethod, $helper->getPaymentMethodVA())) {
            $result['va_number'] = $order->getPayment()->getVaNumber();
        }
        elseif (in_array($paymentMethod, $helper->getPaymentMethodVtdirect())) {
            $result['redirect_url'] = $this->_getAdditionalInfoVtdirect($order);
        }
        
        return $result;
    }
    
    protected function _getAdditionalInfoKlikpay($order) {
        $klikpayConfig = Mage::getStoreConfig('payment/klikpay');
        $klikpayUserId = $klikpayConfig['klikpay_user_id'];
    	$transactionNo = $order->getIncrementId();
    	$currency = 'IDR';
    	$transactionAmount = number_format((int) $order->getGrandTotal(), 2, null, '');
    	$payType = $order->getPayType();
    	//$callBackUrl = Mage::getBaseUrl() . 'success/' . $transactionNo;
    	$callBackUrl = (isset($klikpayConfig['call_back_url']) ? $klikpayConfig['call_back_url'] : FALSE);
    	$transactionDate = date('d/m/Y H:i:s', strtotime($order->getCreatedAt()));
    	$clearKey = $klikpayConfig['klikpay_clearkey'];
    	$signature = Mage::helper('paymethod/klikpay')->signature($klikpayUserId, $transactionNo, $currency, $clearKey, $transactionDate, $transactionAmount);
        $order->setKlikpaySignature(abs($signature))->save();
        
        return [
            'data' => [
                'klikPayCode' => $klikpayUserId,
                'transactionNo' => $transactionNo,
                'totalAmount' => $transactionAmount,
                'currency' => $currency,
                'payType' => $payType,
                'callback' => $callBackUrl,
                'transactionDate' => $transactionDate,
                'descp' => '',
                'miscFee' => '',
                'signature' => abs($signature),
            ],
            'action' => $klikpayConfig['klikpay_redirect'],
        ];
    }
    
    protected function _getAdditionalInfoVtdirect($order) {
        $incrementId = $order->getIncrementId();
        $dbResource = Mage::getSingleton('core/resource');
        $dbRead = $dbResource->getConnection('core_read');
        $sql = "SELECT response FROM `veritrans_api_log` WHERE order_no = '{$incrementId}' AND type = 'C' LIMIT 1";
        $result = '';
        
        if ($row = $dbRead->fetchOne($sql)) {
            $response = json_decode($row, true);
            
            if ($redirectUrl = $response['redirect_url']) {
                $result = $redirectUrl;
            }
        }
        
        return $result;
    }
}
