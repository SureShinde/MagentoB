<?php
/**
 * Description of Bilna_Paymethod_Model_Observer_Webservice_Vtdirect
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Paymethod_Model_Observer_Webservice_Vtdirect {
    public function notificationAction() {
        $notification = json_decode(file_get_contents('php://input'));
        $contentRequest = sprintf("%s | request_vtdirect: %s", $notification->data->order_id, json_encode($notification));
        $this->writeLog($this->_typeTransaction, 'notification', $contentRequest);
        
        /**
         * check order id
         */
        if (!isset ($notification->data->order_id)) {
            $this->writeLog($this->_typeTransaction, 'notification', 'transactionNo failed.');
            exit;
        }
        
        /**
         * check transaction already in process
         */
        if ($this->checkLock($notification->data->order_id)) {
            $this->writeLog($this->_typeTransaction, 'notification', 'Transaction already in process.');
            exit;
        }
        
        /**
         * create lock file
         */
        $this->createLock($notification->data->order_id);
        
        if ($this->getServerKey() == $notification->data->server_key) {
            $order = Mage::getModel('sales/order')->loadByIncrementId($notification->data->order_id);
            
            if ($notification->status == 'success') {
                if ($order->canInvoice()) {
                    $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();

                    if ($invoice->getTotalQty()) {
                        $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE);
                        $invoice->setGrandTotal($order->getGrandTotal());
                        $invoice->setBaseGrandTotal($order->getBaseGrandTotal());
                        $invoice->register();
                        $transactionSave = Mage::getModel('core/resource_transaction')
                            ->addObject($invoice)
                            ->addObject($invoice->getOrder());
                        $transactionSave->save();                            
                        $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true, $notification->message, true)->save();
                        $invoice->sendEmail(true, '');

                        $contentLog = sprintf("%s | status_order: %s", $notification->data->order_id, Mage_Sales_Model_Order::STATE_PROCESSING);
                        $this->writeLog($this->_typeTransaction, 'notification', $contentLog);
                    }
                    else {
                        $contentLog = sprintf("%s | status_order: invoice cannot get total qty", $notification->data->order_id);
                        $this->writeLog($this->_typeTransaction, 'notification', $contentLog);
                    }
                }
                else {
                    $contentLog = sprintf("%s | status_order: cannot create invoice", $notification->data->order_id);
                    $this->writeLog($this->_typeTransaction, 'notification', $contentLog);
                }
            }
            else {
                $order->addStatusHistoryComment($notification->message);
                $order->save();
            }
        }
        else {
            $contentLog = sprintf("%s | status_order: server key is not valid", $notification->data->order_id);
            $this->writeLog($this->_typeTransaction, 'notification', $contentLog);
        }
    }
    
    private function getServerKey() {
        return Mage::getStoreConfig('payment/vtdirect/server_key');
    }
    
    protected function writeLog($type, $logFile, $content) {
        $tdate = date('Ymd', Mage::getModel('core/date')->timestamp(time()));
        $filename = sprintf("%s_%s.%s", $this->_code, $logFile, $tdate);
        
        return Mage::helper('paymethod')->writeLogFile($this->_code, $type, $filename, $content);
    }
    
    protected function createLock($filename) {
        return Mage::helper('paymethod')->createLockFile($this->_code, $filename);
    }
    
    protected function checkLock($filename) {
        return Mage::helper('paymethod')->checkLockFile($this->_code, $filename);
    }
}
