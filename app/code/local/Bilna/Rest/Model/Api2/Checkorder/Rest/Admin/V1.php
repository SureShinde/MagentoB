<?php
/**
 * Description of Bilna_Rest_Model_Api2_Checkorder_Rest_Admin_V1
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Rest_Model_Api2_Checkorder_Rest_Admin_V1 extends Bilna_Rest_Model_Api2_Checkorder_Rest {
    
    protected function _retrieve() {
        $result = $this->_getOrderDetail();
        
        if (!$result) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        
        return $result;
    }
    
    protected function _getOrderDetail() {
        $email = $this->getRequest()->getParam('email');
        $incrementId = $this->getRequest()->getParam('increment_id');
        
        // redirect to form if email & incrementId is empty
        if (!isset ($email) || !isset ($incrementId)) {
            $this->_critical('Invalid Email or order number');
        }
        
        $order = Mage::getModel('sales/order')->load($incrementId, 'increment_id');

        if (!$order) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }

        if ($this->_isPaymentMethodAllowed()) {
            $this->_addPaymentMethodInfo($order);
        }
        $this->_addTaxInfo($order);

        $orderData = $order->getData();
        $orderId = $order->getId();
        $payment = $order->getPayment()->getMethodInstance()->getTitle();
        
        $items     = $this->_getItems(array($orderId));
        
        if ($orderData) {
            $orderData['increment_id'] = $order->getIncrementId();
            $orderData['status'] = $order->getStatus();
        
            if ($order->getSubtotal()) {
                $orderData['subtotal'] = $order->getSubtotal();
            }            
            if($order->getShippingAmount()) {
                $orderData['shipping_amount'] = $order->getShippingAmount();
            }            
            if ($order->getWrappinggiftevent()) {
                $orderData['wrappinggiftevent'] = $order->getWrappinggiftevent();
            }            
            if ($order->getDiscountAmount() && $order->getDiscountAmount() > 0) {
                $orderData['discount_amount'] = $order->getDiscountAmount();
            }
            if($order->getMoneyForPoints()) {
                $orderData['money_for_points'] = $order->getMoneyForPoints();
            }
            
            $orderData['shipping'] = $this->_cleanUpShippingDescription($orderData['shipping_description']);
            $orderData['payment'] = $payment;
        }
        if ($items) {
            $orderData['order_items'] = $items[$orderId];
        }
        
        
        
        return $orderData;
    }
}
