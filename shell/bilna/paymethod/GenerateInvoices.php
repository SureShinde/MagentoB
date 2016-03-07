<?php
/**
 * Description of GenerateMegamenu
 *
 * @author Bilna Development Team <development@bilna.com>
 */

require_once dirname(__FILE__) . '/../../abstract.php';

use Pheanstalk\Pheanstalk;

class GenerateInvoices extends Mage_Shell_Abstract {
    public function run() {
        $hostname = Mage::getStoreConfig('bilna_queue/beanstalkd_settings/hostname');
        $pheanstalk = new Pheanstalk($hostname);
        
        try {
            $pheanstalk->watch('invoice');
            $pheanstalk->ignore('default');
            
            while ($job = $pheanstalk->reserve()) {
                $dataArr = json_decode($job->getData(), true);
                $dataObj = json_decode($job->getData());
                
                $veritransModel = Mage::getModel('paymethod/veritrans');
                $veritransModel->addData($dataArr);
                $veritransModel->insert();
                $veritransModel->save();
                
                $order = Mage::getModel('sales/order')->load($dataObj->order_id);
                $paymentCode = $order->getPayment()->getMethodInstance()->getCode();
                $status = Mage::getModel('paymethod/vtdirect')->updateOrder($order, $paymentCode, $dataObj);
                
                if ($status) {
                    if ($this->queueOrderPlaceForNetsuite($order, $paymentCode)) {
                        $pheanstalk->delete($job);
                    }
                }
                else {
                    $pheanstalk->bury($job);
                }
            }
        }
        catch (Exception $e) {
            $pheanstalk->bury($job);
            Mage::logException($e);
        }
        
        echo "\nFINISH";
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

$shell = new GenerateInvoices();
$shell->run();
