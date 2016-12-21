<?php
/**
 * Description of Bilna_Paymethod_Model_Observer_Klikbca
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Paymethod_Model_Observer_Klikbca {
    protected $_code = 'klikbca';
    protected $_lockFile = 'klikbca_confirm_process';
    protected $_typeTransaction = 'transaction';

    public function confirmationProcess() {
        $this->checkLockProcess();

        $crUrl = Mage::getStoreConfig('payment/klikbca/confirm_url');
        $crUsername = Mage::getStoreConfig('payment/klikbca/confirm_username');
        $crPassword = Mage::getStoreConfig('payment/klikbca/confirm_password');

        $orderCollection = Mage::getModel('sales/order')->getCollection();
        $orderCollection->addFieldToFilter('status', 'klikbca_pending');
        $orderCollection->getSelect()->join(
            ['sfop' => 'sales_flat_order_payment'],
            "sfop.method = '{$this->_code}' AND sfop.parent_id = main_table.entity_id",
            ['klikbca_user_id']
        );
        foreach ($orderCollection as $order) {
            $this->orderProcess($order, $crUrl, $crUsername, $crPassword);
        }

        $this->removeLockProcess();
    }

    private function orderProcess($order, $crUrl, $crUsername, $crPassword)
    {
        $klikbcaUserId = $order->getKlikbcaUserId();
        $transactionNo = $order->getIncrementId();
        $crData = [
            'cru' => $crUsername,
            'crp' => $crPassword,
            'userid' => $klikbcaUserId,
            'transno' => $transactionNo,
            'transdate' => date('m/d/Y H:i:s', strtotime($order->getCreatedAt())),
            'amount' => 'IDR' . number_format((int)$order->getGrandTotal(), 2, null, ''),
            'type' => 'N',
            'adddata' => '',
            'rettype' => 'xml'
        ];

        $contentLog = sprintf("%s | request_bilna: %s", $klikbcaUserId, json_encode($crData));
        $this->writeLog($this->_typeTransaction, 'confirmation', $contentLog);

        $response = Mage::helper('paymethod/klikbca')->postRequest($crUrl, $crData);
        $contentLog = sprintf("%s | response_bilna: %s", $klikbcaUserId, json_encode($response));
        $this->writeLog($this->_typeTransaction, 'confirmation', $contentLog);

        /**
         * ubah response xml menjadi object
         */
        $responseObj = simplexml_load_string($response);
        if ($responseObj->status == '00') {
            /**
             * status pembayaran sukses
             * update order status menjadi "Processing", dan create lock file berdasarkan TransactionNo
             */
            $updateOrderStatus = $this->_updateOrderStatus($order, 'processing');
            if ($updateOrderStatus) {
                $contentLog = sprintf("%s | order_status: %s -> processing", $klikbcaUserId, $transactionNo);
                $this->writeLog($this->_typeTransaction, 'confirmation', $contentLog);
                $this->moveFile($filename, $transactionNo, 'success');
            }
        } else if ($responseObj->status == '01') {
            /**
             * status pembayaran gagal, customer dapat membayar kembali
             * update order status menjadi "Pending" dan kirim email notifikasi ke customer
             */
            $this->_updateOrderStatus($order, 'pending');
            $order->addStatusHistoryComment('Konfirmasi SprintAsia: ' . $responseObj->reason);

            $templateId = Mage::getStoreConfig('payment/klikbca/template_id_pending');
            $emailVars = array (
                'email_to' => $order->getCustomerEmail(),
                'name_to' => $order->getCustomerName(),
                'transaction_no' => $transactionNo
            );
            Mage::helper('paymethod/klikbca')->_sendEmail($templateId, $emailVars);

            $contentLog = sprintf("%s | order_status: %s -> pending", $klikbcaUserId, $transactionNo);
            $this->writeLog($this->_typeTransaction, 'confirmation', $contentLog);
            $this->moveFile($filename, $transactionNo, 'failed');
        } else {
            /**
             * kesalahan pada Username/Password
             * invalid credential. akan diproses pada cron berikutnya
             * add comment
             */
            $order->addStatusHistoryComment('Konfirmasi SprintAsia: ' . $responseObj->reason);
            $order->save();
        }
    }

    private function _updateOrderStatus($order, $orderStatus = 'processing') {
        if ($orderStatus == 'processing') {
            if ($order->canInvoice()) {
                $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();

                if ($invoice->getTotalQty()) {
                    $invoice->setGrandTotal($order->getGrandTotal());
                    $invoice->setBaseGrandTotal($order->getBaseGrandTotal());
                    $invoice->register();
                    $transactionSave = Mage::getModel('core/resource_transaction')
                        ->addObject($invoice)
                        ->addObject($invoice->getOrder());
                    $transactionSave->save();
                    $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true, Mage::getStoreConfig('payment/klikbca/order_processing_comment'), true)->save();
                    $invoice->sendEmail(true, '');

                    return true;
                }
            }

            return false;
        }
        else {
            // order status diset menjadi Pending dan kirim email notifikasi ke customer
            $order->setState(Mage_Sales_Model_Order::STATE_NEW, 'pending', Mage::getStoreConfig('payment/klikbca/order_pending_comment'), true)->save();

            return true;
        }
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
