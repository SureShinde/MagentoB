<?php
/**
 * Description of Bilna_Sales_Model_Api2_Order_Info_Rest
 *
 * @author  Bilna Development Team <development@bilna.com>
 * @path    app/code/local/Bilna/Sales/Model/Api2/Order/Info/Rest.php
 */

abstract class Bilna_Sales_Model_Api2_Order_Info_Rest extends Bilna_Sales_Model_Api2_Order_Info {
    protected function _getOrder($orderId) {
        return Mage::getModel('sales/order')->load($orderId);
    }
    
    protected function _getOrderStatus($order) {
        $statusCode = $order->getStatus();
        $status = Mage::getModel('sales/order_status')->load($statusCode);
        
        return $status->getLabel();
    }

    protected function _getPaymentInfo($paymentMethod) {
        return true;
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
        
        if (in_array($paymentMethod, $helper->getPaymentMethodVA())) {
            $result['va_number'] = $order->getPayment()->getVaNumber();
        }
        
        return $result;
    }
}
