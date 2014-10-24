<?php
/**
 * Description of Bilna_Paymethod_Model_Observer_Webservice_Vtdirect
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Paymethod_Model_Observer_Webservice_Vtdirect {
    protected $_code = 'vtdirect';
    protected $_typeTransaction = 'transaction';
    
    public function notificationAction() {
        $notification = json_decode(file_get_contents('php://input'));
        $incrementId = $notification->order_id;
        $contentRequest = sprintf("%s | request_vtdirect: %s", $incrementId, json_encode($notification));
        $this->writeLog($this->_typeTransaction, 'notification', $contentRequest);
        
        /**
         * check order id
         */
        if (!isset ($incrementId)) {
            $this->writeLog($this->_typeTransaction, 'notification', $incrementId . ': orderid is empty.');
            exit;
        }
        
        /**
         * check transaction already in process
         */
        //if ($this->checkLock($incrementId)) {
        //    $this->writeLog($this->_typeTransaction, 'notification', $incrementId . ': Transaction already in process.');
        //    exit;
        //}
        
        // create lock file
        //$this->createLock($incrementId);
        
        $order = Mage::getModel('sales/order')->loadByIncrementId($incrementId);
        $message = $notification->status_message;
        $transactionStatus = $notification->transaction_status;
        $fraudStatus = $notification->fraud_status;
        
        // skip if notification is 'challange' and 'cancel'
        if (($transactionStatus == 'capture' && $fraudStatus == 'challenge') || ($transactionStatus == 'challenge') || ($transactionStatus == 'cancel')) {
            $contentLog = sprintf("%s | skip order proccess", $incrementId);
            $this->writeLog($this->_typeTransaction, 'notification', $contentLog);
            //$this->removeLock($incrementId);
            //exit;
        }
        else {
            if (Mage::getModel('paymethod/vtdirect')->updateOrder($order, $paymentCode, $notification)) {
                $contentLog = sprintf("%s | status_order: %s", $incrementId, $order->getStatus());
                $this->writeLog($this->_typeTransaction, 'notification', $contentLog);
            }
            else {
                $contentLog = sprintf("%s | status_order: failed", $incrementId);
                $this->writeLog($this->_typeTransaction, 'notification', $contentLog);
                //$this->removeLock($incrementId);    
            }
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
    
    protected function removeLock($filename) {
        return Mage::helper('paymethod')->removeLockFile($this->_code, $filename);
    }
}
