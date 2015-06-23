<?php
/**
 * Description of Bilna_Paymethod_Block_Klikpay_Thankyou
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Paymethod_Block_Klikpay_Thankyou extends Mage_Checkout_Block_Onepage_Success {
    public function _getTransactionNo() {
        return $this->getRequest()->getParam('id');
    }
    
    public function _getOrderDetail() {
        $transactionNo = $this->getRequest()->getParam('id');
        $order = Mage::getModel('sales/order')->loadByIncrementId($transactionNo);
        $orderStatus = $order->getStatus();
        $orderData = $order->_data;
        $entityId = $orderData['entity_id'];
        $success = $orderStatus == 'processing' ? true : false;
        
        $result = array (
            'id' => $transactionNo,
            'status' => $orderStatus,
            'view_url' => $this->_getViewUrl($entityId),
            'print_url' => $this->_getPrintUrl($entityId),
            'pay_url' => $this->_getPayUrl($transactionNo),
            'success' => $success
        );
        
        return (object) $result;
    }
    
    protected function _getViewUrl($entityId) {
        return sprintf("%ssales/order/view/order_id/%d/", Mage::getBaseUrl(), $entityId);
    }
    
    protected function _getPrintUrl($entityId) {
        return sprintf("%ssales/order/print/order_id/%d/", Mage::getBaseUrl(), $entityId);
    }
    
    protected function _getPayUrl($transactionNo) {
        return sprintf("%sklikpay/processing/pay/id/%s/", Mage::getBaseUrl(), $transactionNo);
    }
}
