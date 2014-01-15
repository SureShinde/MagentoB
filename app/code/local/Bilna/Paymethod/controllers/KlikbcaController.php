<?php
/**
 * Description of Bilna_Paymethod_KlikbcaController
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Paymethod_KlikbcaController extends Mage_Core_Controller_Front_Action {
    protected $_code = 'klikbca';
    protected $_currency = 'IDR';
    protected $_formatDate = 'Y-m-d H:i:s';
    protected $_formatDate2 = 'm/d/Y H:i:s';
    
    protected $_statusTrxSuccess = '00';
    protected $_statusTrxFailed = '01';
    
    protected $_reasonTrxSuccess = 'Success.';
    protected $_reasonTrxFailed = 'Your transaction cannot be processed.';
    protected $_reasonTrxPaid = 'Your transaction has been paid.';
    protected $_reasonTrxExpired = 'Your transaction has expired.';
    
    protected $_typeTransaction = 'transaction';
    protected $_typeConfirmation = 'confirmation';

    public function inquiryAction() {
        $klikbcaUserId = $this->getRequestData('userid');
        $additionalData = $this->getRequestData('adddata');
        
        $contentLog = sprintf("%s | request_klikbca: %s", $klikbcaUserId, json_encode($this->getRequest()->getParams()));
        $this->writeLog($this->_typeTransaction, 'inquiry', $contentLog);
        
        $validationHours = Mage::getStoreConfig('payment/klikbca/order_validation');
        $validationOption = Mage::getStoreConfig('payment/klikbca/order_validation_option');
        
        $orderCollection = Mage::getModel('sales/order')->getCollection();
        $orderCollection->addFieldToFilter('status', 'pending');
        $orderCollection->getSelect()->join(array ('SFOP' => 'sales_flat_order_payment'), 'klikbca_user_id="' . $klikbcaUserId . '" AND SFOP.method="' . $this->_code . '" AND main_table.entity_id=SFOP.parent_id', array ('klikbca_user_id'), null, 'left');
        
        header('Content-type: text/xml; charset=utf-8');
        $xml  = '<?xml version="1.0" encoding="utf-8"?>';
        $xml .= '<OutputListTransactionPGW>';
        $xml .= "<userID>" . $klikbcaUserId . "</userID>";
        $xml .= "<additionalData>" . $additionalData . "</additionalData>";
        
        foreach ($orderCollection as $order) {
            if ($validationHours) {
            	$orderCreatedDate = $order->getCreatedAt();
                $orderCreatedDatePlus = date($this->_formatDate, strtotime($orderCreatedDate . ' + ' . $validationHours . ' ' . $validationOption));
                $now = date($this->_formatDate);

                if (strtotime($now) > strtotime($orderCreatedDatePlus)) {
                    continue;
                }
            }
            
            $items = $order->getAllItems();
            $productName = '';
            
            foreach ($items as $itemId => $item) {
                $productName = $item->getName();
                break;
            }

            $xml .= "<OutputDetailPayment>";
            $xml .= "<transactionNo>" . $order->getIncrementId() . "</transactionNo>";
            $xml .= "<transactionDate>" . date($this->_formatDate2, strtotime($order->getCreatedAt())) . "</transactionDate>";
            $xml .= "<amount>" . $this->_currency . number_format((int) $order->getGrandTotal(), 2, null, '') . "</amount>";
            $xml .= "<description>" . Mage::helper('paymethod/klikbca')->_removeSymbols($productName) . "</description>";
            $xml .= "</OutputDetailPayment>";                     
        }

        $xml .= "</OutputListTransactionPGW>";
        
        $contentLog = sprintf("%s | response_klikbca: %s", $_GET['userid'], $xml);
        $this->writeLog($this->_typeTransaction, 'inquiry', $contentLog);
        die ($xml);
    }
    
    public function paymentAction() {
        $klikbcaUserId = $this->getRequestData('userid');
        $transactionNo = $this->getRequestData('transno');
        $transactionDate = $this->getRequestData('transdate');
        $transactionAmount = $this->getRequestDataReplace('amount', $this->_currency);
        $transactionAmountCurrency = $this->getRequestData('amount');
        $type = $this->getRequestData('type');
        $additionalData = $this->getRequestData('adddata');
        $reason = '';
        $status = '';
        
        $contentLog = sprintf("%s | request_klikbca: %s", $klikbcaUserId, json_encode($this->getRequest()->getParams()));
        $this->writeLog($this->_typeTransaction, 'payment', $contentLog);
        
        $orderCollection = Mage::getModel('sales/order')->getCollection();
        $orderCollection->addFieldToFilter('increment_id', array ('eq' => $transactionNo));
        $orderCollection->getSelect()->join(array ('SFOP' => 'sales_flat_order_payment'), 'klikbca_user_id = "' . $klikbcaUserId . '" AND SFOP.method = "' . $this->_code . '" AND main_table.entity_id = SFOP.parent_id', array ('klikbca_user_id'), null, 'left')->limit(1);
        
        $order = $orderCollection->getFirstItem();
        $validationHours = Mage::getStoreConfig('payment/klikbca/order_validation');
        $validationOption = Mage::getStoreConfig('payment/klikbca/order_validation_option');
        $validHours = 'yes';

        if ($validationHours) {
            $orderCreatedDate = $order->getCreatedAt();
            $orderCreatedDatePlus = date($this->_formatDate, strtotime($orderCreatedDate . ' + ' . $validationHours . ' ' . $validationOption));
            $now = date($this->_formatDate);

            if (strtotime($now) > strtotime($orderCreatedDatePlus)) {
                $validHours = 'no';
            }
        }
        
        if ($order->getId()) {
            if (number_format((int) $order->getGrandTotal(), 2, null, '') != $transactionAmount) {
                $reason = $this->_reasonTrxFailed;
            }
            else if ($order->getStatus() == 'processing') {
                $reason = $this->_reasonTrxPaid;
            }
            else if ($order->getStatus() == 'klikbca_pending' || $order->getStatus() == 'canceled') {
                $reason = $this->_reasonTrxFailed;
            }
            else if ($validHours == 'no') {
                $reason = $this->_reasonTrxExpired;
            }
            else {
               // create invoice
               try {
                   $order->setState(Mage_Sales_Model_Order::STATE_NEW, 'klikbca_pending', Mage::getStoreConfig('payment/klikbca/order_klikbca_pending_comment'), true)->save();
                   $status = $this->_statusTrxSuccess;
                   $reason = $this->_reasonTrxSuccess;
                   
                   $contentLog = sprintf("%s|%s|%s|%s|%s|%s", $klikbcaUserId, $transactionNo, $transactionDate, $transactionAmountCurrency, $type, $additionalData);
                   $this->createLog($transactionNo, 'confirmation', $contentLog);
                }
                catch (Mage_Core_Exception $e) {
                    Mage::log($e->getMessage(), null, 'klikbca_error.log', true);
                }
            }
        }
        else {
            $reason = $this->_reasonTrxFailed;
        }
        
        header('Content-type: text/xml; charset=utf-8');
        $xml  = '<?xml version="1.0" encoding="utf-8"?><OutputPaymentPGW>';
        $xml .= "<userID>" . $klikbcaUserId . "</userID>";
        $xml .= "<transactionNo>" . $transactionNo . "</transactionNo>";
        $xml .= "<transactionDate>" . $transactionDate . "</transactionDate>";
        $xml .= "<status>" . $status . "</status>";
        $xml .= "<reason>" . $reason . "</reason>";
        $xml .= "<additionalData>" . $additionalData . "</additionalData>";
        $xml .= "</OutputPaymentPGW>";
        
        $contentLog = sprintf("%s | response_klikbca: %s", $klikbcaUserId, $xml);
        $this->writeLog($this->_typeTransaction, 'payment', $contentLog);
        die ($xml);
    }
    
    protected function getRequestData($key) {
        $result = '';
        
        if ($this->getRequest()->getParam($key)) {
            $result = $this->getRequest()->getParam($key);
        }
        
        return $result;
    }
    
    protected function getRequestDataReplace($key, $replace) {
        $result = '';
        
        if ($this->getRequest()->getParam($key)) {
            $result = $this->getRequest()->getParam($key);
            
            if (strpos($result, $replace) !== false) {
                $result = str_replace($replace, '', $result);
            }
        }
        
        return $result;
    }
    
    protected function writeLog($type, $logFile, $content) {
        $tdate = date('Ymd', Mage::getModel('core/date')->timestamp(time()));
        $filename = sprintf("%s_%s.%s", $this->_code, $logFile, $tdate);
        
        return Mage::helper('paymethod')->writeLogFile($this->_code, $type, $filename, $content);
    }
    
    protected function createLog($filename, $type, $content) {
        $tdate = date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(time()));
        $content = sprintf("%s|%s", $content, $tdate);
        
        return Mage::helper('paymethod')->writeLogFile($this->_code, $type, $filename, $content, 'normal');
    }
}
