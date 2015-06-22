<?php
class Bilna_Veritrans_NotifyController extends Mage_Core_Controller_Front_Action {
    protected $_code = 'veritrans';
    protected $_file = 'notify';

    public function HandlingAction() {
        $this->writeTransactionLog(sprintf("%s | request_veritrans: %s", $this->getRequest()->getPost('orderId'), json_encode($this->getRequest()->getPost())));

        $mStatus = strtolower($this->getRequest()->getPost('mStatus'));
        $orderId = $this->getRequest()->getPost('orderId');
        $tokenMerchant = $this->getRequest()->getPost('TOKEN_MERCHANT');
        
        /**
         * check request spam
         */
        if (!isset ($mStatus) || !isset ($orderId) || !isset ($tokenMerchant)) {
            $this->writeTransactionLog('spam notify');
            exit;
        }

        /**
         * cek lock file
         * e.g veritrans.100000775.lock
         */
        if ($this->checkLockFile($orderId) === false) {
            $this->writeTransactionLog(sprintf("%s | Order has been locked.", $orderId));
            exit;
        }
        
        /**
         * validasi token_merchant (didapat pada saat call api veritrans)
         */
        if (!$this->validateRequestData($orderId, $tokenMerchant)) {
            $this->removeLockFile($orderId);
            $this->writeTransactionLog(sprintf("%s | Token Merchant doesnot match.", $orderId));
            exit;
        }
        
        if ($mStatus) {
            /**
             * update table veritrans_track
             */
            if (!$this->updateNotifyData($orderId)) {
                $this->removeLockFile($orderId);
                exit;
            }
            
            $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
            
            /**
             * status from veritrans:
             * - success
             * - challenge
             * - failure
             */
            if ($mStatus == 'success') {
                try {
                    if (!$order->canInvoice()) {
                        $this->removeLockFile($orderId);
                        $this->writeTransactionLog(sprintf("%s | status_order: %s", $orderId, "Cannot create an invoice."));
                        exit;
                    }

                    $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();

                    if (!$invoice->getTotalQty()) {
                        $this->removeLockFile($orderId);
                        $this->writeTransactionLog(sprintf("%s | status_order: %s", $orderId, "Cannot create an invoice without products."));
                        exit;
                    }

                    $invoice->register();
                    $transactionSave = Mage::getModel('core/resource_transaction')->addObject($invoice)->addObject($invoice->getOrder());
                    $invoice->sendEmail(true, '');
                    $transactionSave->save();                            
                    $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true)->save();
                    $this->writeTransactionLog(sprintf("%s | status_order: %s", $orderId, "Invoice created successfully."));
                }
                catch (Mage_Core_Exception $e) {
                    $this->writeTransactionLog(sprintf("%s | status_order: %s | %s", $orderId, "Invoice created failed.", $e->getMessage()));
                    exit;
                }
            }
            else if ($mStatus == 'challenge') {
                try {
                    // set status order and add comment
                    $comment = Mage::getStoreConfig('payment/veritrans/challenge_comment');
                    $order->setState(Mage_Sales_Model_Order::STATE_NEW, 'cc_verification', $comment, true);
                    $order->save();
                    $order->sendOrderUpdateEmail(true, $comment); // sending email
                    $this->writeTransactionLog(sprintf("%s | status_order: %s", $orderId, "set payment review and send email notify."));
                }
                catch (Mage_Core_Exeption $e) {
                    $this->writeTransactionLog(sprintf("%s | status_order: %s", $orderId, $e->getMessage()));
                    exit;
                }
            }
            else {
                // if mStatus is 'failure'
                try {
                    if ($order->canCancel()) {
                        $comment = Mage::getStoreConfig('payment/veritrans/failure_comment');
                        $order
                            ->cancel()
                            ->setState(Mage_Sales_Model_Order::STATE_CANCELED, true, $comment, true)
                            ->save()
                            ->sendOrderUpdateEmail(true, $comment);
                        $this->writeTransactionLog(sprintf("%s | status_order: %s", $orderId, "Payment order failed and set canceled."));
                    }
                    else {
                        $this->removeLockFile($orderId);
                        $this->writeTransactionLog(sprintf("%s | status_order: %s", $orderId, "Payment order failed and order cannot cancel."));
                        exit;
                    }
                }
                catch (Mage_Core_Exeption $e) {
                    $this->writeTransactionLog(sprintf("%s | status_order: %s", $orderId, $e->getMessage()));
                    exit;
                }
            }
        }
        else {
            $this->removeLockFile($orderId);
            $this->writeTransactionLog(sprintf("%s | status_order: %s", $orderId, "mStatus not defined."));
            exit;
        }
    }
    
    protected function validateRequestData($orderId, $tokenMerchant) {
        $dataWhere = array (
            'order_id' => $orderId,
            'token_merchant' => $tokenMerchant
        );
        $rows = Mage::getModel('veritrans/veritrans')->setData($dataWhere)->selectData('id')->fetch();

        if (is_array($rows) && count($rows) > 0) {
            return true;
        }

        return false;
    }
    
    protected function updateNotifyData($orderId) {
        $dataSet = array (
            'req_mStatus' => strtolower($this->getRequest()->getPost('mStatus')),
            'req_maskedCardNumber' => $this->getRequest()->getPost('maskedCardNumber'),
            'req_mErrMsg' => $this->getRequest()->getPost('mErrMsg'),
            'req_vResultCode' => $this->getRequest()->getPost('vResultCode')
        );
        $dataWhere = array (
            'order_id' => $orderId,
            'token_merchant' => $this->getRequest()->getPost('TOKEN_MERCHANT')
        );
        
        if (Mage::getModel('veritrans/veritrans')->setData($dataSet)->updateData($dataWhere)->save()) {
            $this->writeTransactionLog(sprintf("%s | update table veritrans_track: %s", $orderId, "update success."));
            return true;
        }
        
        $this->writeTransactionLog(sprintf("%s | update table veritrans_track: %s", $orderId, "update failed."));
        return false;
    }
    
    protected function checkLockFile($orderId) {
        $filename = sprintf("%s.%s", $this->_code, $orderId);
        
        return Mage::helper('veritrans')->checkLockFile($filename);
    }
    
    protected function removeLockFile($orderId) {
        $filename = sprintf("%s.%s", $this->_code, $orderId);
        
        return Mage::helper('veritrans')->removeLockFile($filename);
    }


    protected function writeTransactionLog($content) {
        $trxLogPath = Mage::helper('veritrans')->getTrxLogPath();
        $filename = sprintf(
            "%s_%s.%s",
            $this->_code,
            $this->_file,
            date('Ymd')
        );

        return Mage::helper('veritrans')->writeLogFile($trxLogPath, $filename, $content);
    }
}
