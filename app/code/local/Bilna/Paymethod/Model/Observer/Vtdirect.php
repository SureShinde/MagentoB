<?php
/**
 * Description of Bilna_Paymethod_Model_Observer_Vtdirect
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Paymethod_Model_Observer_Vtdirect {
    protected $_code = 'vtdirect';
    protected $_lockFile = 'vtdirect_confirm_process';
    protected $_baseLogPath = '';
    protected $_confirmLogPath = '';
    protected $_successLogPath = '';
    protected $_logPath = '';
    protected $_typeTransaction = 'transaction';
    
    public function confirmationProcess() {
        $this->checkLockProcess();
        $this->setPath();
        
        /**
         * read directory
         */
        if ($handle = opendir($this->_logPath)) {
            while (false !== ($orderFile = readdir($handle))) {
                if ($orderFile != "." && $orderFile != "..") {
                    $this->orderProcess($orderFile);
                }
            }

            closedir($handle);
        }
        
        $this->removeLockProcess();
    }
    
    private function orderProcess($orderFile) {
        $filename = $this->_logPath . $orderFile;
        $orderDetail = file($filename, FILE_IGNORE_NEW_LINES);
        
        if ($orderDetail !== false) {
            $orderDetailArr = explode('|', $orderDetail[0]);
            $transactionNo = $orderDetailArr[0];
            $crUrl = $url = Mage::getStoreConfig('payment/vtdirect/charge_transaction_status_url');
            $crData = array ('order_id' => $transactionNo);
            
            $contentLog = sprintf("%s | request_bilna: %s", $transactionNo, json_encode($crData));
            $this->writeLog($this->_typeTransaction, 'confirmation', $contentLog);
            
            $response = json_decode(Mage::helper('paymethod/vtdirect')->postRequest($crUrl, $crData));
            $contentLog = sprintf("%s | response_bilna: %s", $transactionNo, json_encode($response));
            $this->writeLog($this->_typeTransaction, 'confirmation', $contentLog);
            
            /**
             * handling response
             */
            $order = Mage::getModel('sales/order')->loadByIncrementId($transactionNo);
            $updateOrder = $this->updateOrder($transactionNo, $order, $response, $filename);
            
            if ($updateOrder) {
                $contentLog = sprintf("%s | update order status success", $transactionNo);
                $this->writeLog($this->_typeTransaction, 'confirmation', $contentLog);
                //$this->moveFile($filename, $transactionNo, 'success');
            }
        }
    }
    
    private function updateOrder($transactionNo, $order, $response) {
        if ($response->status == 'success') {
            $transactionStatus = $response->data->transaction_status;
            $message = sprintf("response veritrans: %s", $response->message);

            if ($transactionStatus == 'deny') {
                $order->addStatusHistoryComment($message);
                $order->save();
                $this->moveFile($filename, $transactionNo, 'deny');
                
                return true;
            }
            else if ($transactionStatus == 'cancel') {
                $order->addStatusHistoryComment($message);
                $order->save();
                $this->moveFile($filename, $transactionNo, 'cancel');
                
                return true;
            }
            else if ($transactionStatus == 'challenge') {
                $order->setState(Mage_Sales_Model_Order::STATE_NEW, 'cc_verification', $message, true);
                $order->save();
                $this->moveFile($filename, $transactionNo, 'challenge');
                
                return true;
            }
            else if ($transactionStatus == 'authorize') {
                if ($order->canInvoice()) {
                    $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();

                    if ($invoice->getTotalQty()) {
                        $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE);
                        $invoice->setGrandTotal($order->getGrandTotal());
                        $invoice->setBaseGrandTotal($order->getBaseGrandTotal());
                        $invoice->register();
                        $transaction = Mage::getModel('core/resource_transaction')
                            ->addObject($invoice)
                            ->addObject($invoice->getOrder());
                        $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true, $message, true);
                        $order->save();
                        $transaction->save();
                        $invoice->sendEmail(true, '');
                        $this->moveFile($filename, $transactionNo, 'authorize');

                        return true;
                    }
                }
            }
            else if ($transactionStatus == 'settlement') {
                $order->addStatusHistoryComment($message);
                $order->save();
                $this->moveFile($filename, $transactionNo, 'settlement');
                
                return true;
            }
            else {
                return false;
            }
        }
        else if ($response->status == 'failure') {
            $order->addStatusHistoryComment($response->message);
            $order->save();
            $this->moveFile($filename, $transactionNo, 'failure');

            return true;
        }
        else {
            $order->addStatusHistoryComment($response->message);
            $order->save();
            $this->moveFile($filename, $transactionNo, $response->status);
            
            $contentLog = sprintf("%s | other response status", $transactionNo);
            $this->writeLog($this->_typeTransaction, 'confirmation', $contentLog);
            
            return false;
        }
    }

    protected function setPath() {
        $this->_baseLogPath = sprintf("%s/%s", Mage::getBaseDir(), Mage::getStoreConfig('bilna_module/paymethod/log_path'));
        $this->_confirmLogPath = Mage::getStoreConfig('payment/vtdirect/confirm_log_path');
        $this->_successLogPath = Mage::getStoreConfig('payment/vtdirect/success_log_path');
        $this->_logPath = $this->_baseLogPath . $this->_confirmLogPath;
    }
    
    protected function checkLockProcess() {
        /**
         * check lock file process
         */
        if (Mage::helper('paymethod')->checkLockFile($this->_code, $this->_lockFile)) {
            $contentLog = "cannot start process because other process still running";
            $this->writeLog($this->_typeTransaction, 'confirmation', $contentLog);
            exit;
        }
        else {
            /**
             * create lock file process
             */
            if (Mage::helper('paymethod')->createLockFile($this->_code, $this->_lockFile)) {
                return true;
            }
            else {
                $contentLog = "cannot create lock file process";
                $this->writeLog($this->_typeTransaction, 'confirmation', $contentLog);
                exit;
            }
        }
    }
    
    protected function removeLockProcess() {
        if (Mage::helper('paymethod')->removeLockFile($this->_code, $this->_lockFile)) {
            return true;
        }
        else {
            $contentLog = "cannot remove lock file process";
            $this->writeLog($this->_typeTransaction, 'confirmation', $contentLog);

            return false;
        }
    }

    protected function writeLog($type, $logFile, $content) {
        $tdate = date('Ymd', Mage::getModel('core/date')->timestamp(time()));
        $filename = sprintf("%s_%s.%s", $this->_code, $logFile, $tdate);
        
        return Mage::helper('paymethod')->writeLogFile($this->_code, $type, $filename, $content);
    }
    
    protected function moveFile($oldFilename, $newFilename, $type) {
        return Mage::helper('paymethod')->moveFile($oldFilename, $newFilename, $this->_code, $type);
    }
}
