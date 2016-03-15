<?php
/**
 * Description of Bilna_Worker_Order_GenerateInvoices
 *
 * @author Bilna Development Team <development@bilna.com>
 */

require_once dirname(__FILE__) . '/Order.php';

class Bilna_Worker_Order_GenerateInvoices extends Bilna_Worker_Order_Order {
    protected $_tubeAllow = 'invoice';

    protected function _process() {
        try {
            $this->_queueSvc->watch($this->_tubeAllow);
            $this->_queueSvc->ignore($this->_tubeIgnore);

            while ($job = $this->_queueSvc->reserve()) {
                $dataObj = json_decode($job->getData());
                $incrementId = $dataObj->order_id;
                $order = Mage::getModel('sales/order')->loadByIncrementId($incrementId);
                
                if (!$order) {
                    $this->_queueSvc->delete($job);
                    $this->_logProgress("#{$incrementId} Process Invoice => failed, Order not found.");
                    continue;
                }
                
                $paymentCode = $order->getPayment()->getMethodInstance()->getCode();
                $status = Mage::getModel('paymethod/vtdirect')->updateOrder($order, $paymentCode, $dataObj);
                
                if ($status && $this->queueOrderPlaceForNetsuite($order, $paymentCode)) {
                    $this->_queueSvc->delete($job);
                    $this->_logProgress("#{$incrementId} Process Invoice => success");
                }
                else {
                    $this->_queueSvc->bury($job);
                    $this->_logProgress("#{$incrementId} Process Invoice => failed");
                }
            }
        }
        catch (Exception $ex) {
            $this->_queueSvc->bury($job);
            $this->_logProgress($ex->getMessage());
        }
    }

    /**
     * Sends new order to netsuite
     * @param $observer
     * @return $this
     */
    private function queueOrderPlaceForNetsuite($order, $paymentCode) {
        if (!Mage::helper('rocketweb_netsuite')->isEnabled()) {
            return false;
        }
        
        if (!Mage::helper('rocketweb_netsuite/permissions')->isFeatureEnabled(RocketWeb_Netsuite_Helper_Permissions::SEND_ORDERS)) {
            return false;
        }
        
        if ($this->checkQueueOrderPlace($order, $paymentCode)) {
            $orderId = $order->getId();
            $incrementId = $order->getIncrementId();
            
            //- adding logging for tracking fake order
            $msg = "\n".date('YmdHis')." increment id ".$incrementId. " | entity_id ".$orderId ." | paymentMethodCode ".$paymentCode;
            error_log($msg, 3, Mage::getBaseDir('var') . DS . 'log'.DS.'netsuite_order.log');
            //- end adding logger for fake order
            
            $message = Mage::getModel('rocketweb_netsuite/queue_message');
            $message->create(RocketWeb_Netsuite_Model_Queue_Message::ORDER_PLACE, $orderId, RocketWeb_Netsuite_Helper_Queue::NETSUITE_EXPORT_QUEUE);
            Mage::helper('rocketweb_netsuite/queue')->getQueue(RocketWeb_Netsuite_Helper_Queue::NETSUITE_EXPORT_QUEUE)->send($message->pack());
        }
        
        return true;
    }
    
    private function checkQueueOrderPlace($order, $paymentCode) {
        $paymentCheck = explode(',', Mage::getStoreConfig('rocketweb_netsuite/exports/order_payment_check'));
        
        if (in_array($paymentCode, $paymentCheck)) {
            $orderStatusAllow = explode(',', Mage::getStoreConfig('rocketweb_netsuite/exports/order_status_allow'));
            $orderStatus = $order->getStatus();
            
            if (in_array($orderStatus, $orderStatusAllow)) {
                return true;
            }
            
            return false;
        }
        
        return true;
    }
}

$worker = new Bilna_Worker_Order_GenerateInvoices();
$worker->run();
