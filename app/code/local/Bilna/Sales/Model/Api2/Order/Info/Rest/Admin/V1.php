<?php
/**
 * Description of Bilna_Sales_Model_Api2_Order_Info_Rest_Admin_V1
 *
 * @author  Bilna Development Team <development@bilna.com>
 * @path    app/code/local/Bilna/Sales/Model/Api2/Order/Payment/Rest/Admin/V1.php
 */

class Bilna_Sales_Model_Api2_Order_Info_Rest_Admin_V1 extends Bilna_Sales_Model_Api2_Order_Info_Rest {
    protected function _retrieve() {
        try {
            $orderId = $this->getRequest()->getParam('order_id');
            $order = $this->_getOrder($orderId);
            
            if (!$order) {
                $this->_critical(self::RESOURCE_NOT_FOUND);
            }
            
            $helper = Mage::helper('paymethod');
            $incrementId = $order->getIncrementId();
            $status = $this->_getOrderStatus($order);
            $isCanceled = $this->_isCanceled($order->getState());
            $paymentMethod = $order->getPayment()->getMethod();
            $paymentInfo = $this->_getPaymentInfo($paymentMethod);
            $isCreditCard = $isCanceled ? false : $this->_isCreditCard($helper, $paymentMethod);
            $isRedirect = $isCanceled ? false : $this->_isRedirect($helper, $paymentMethod);
            $additionalInfo = $isCanceled ? [] : $this->_getAdditionalInfo($helper, $order, $paymentMethod);
            
            $result = [
                'entity_id' => $orderId,
                'increment_id' => $incrementId,
                'status' => $status,
                'payment_method' => $paymentMethod,
                'payment_info' => $paymentInfo,
                'credit_card' => $isCreditCard,
                'redirect' => $isRedirect,
                'additional_info' => $additionalInfo,
            ];
            
            return $result;
        }
        catch (Exception $e) {
            $this->_critical($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        }
    }
}
