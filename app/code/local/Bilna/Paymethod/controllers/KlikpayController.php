<?php
/**
 * Description of Bilna_Paymethod_KlikpayController
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Paymethod_KlikpayController extends Mage_Core_Controller_Front_Action {
    protected $_code = 'klikpay';
    protected $_typeTransaction = 'transaction';
    protected $_typeConfirmation = 'confirmation';

    public function redirectAction() {
        $this->loadLayout();
        $this->getLayout()->getBlock('head')->setTitle($this->__('Redirect Page'));
        $this->renderLayout();
    }
    
    public function inquiryAction() {
        $this->writeLog($this->_typeTransaction, 'inquiry', 'request_method: ' . $this->getRequestMethod());
        
        $klikpayUserId = $this->getRequestData('klikPayCode');
        $additionalData = $this->getRequestData('additionalData');
        $transactionNo = $this->getRequestData('transactionNo');
        $signature = $this->getRequestData('signature');
        
        $contentLog = sprintf("%s | request_klikpay: %s", $transactionNo, json_encode($this->getRequestData()));
        $this->writeLog($this->_typeTransaction, 'inquiry', $contentLog);
        
        $orderCollection = Mage::getModel('sales/order')->getCollection();
        $orderCollection->addFieldToFilter('status', 'pending');
        $orderCollection->addFieldToFilter('increment_id', array ('eq' => $transactionNo));
        $orderCollection->getSelect()->join(array ('SFOP' => 'sales_flat_order_payment'), 'SFOP.method = "klikpay" AND main_table.entity_id = SFOP.parent_id', array ('method'), null, 'left');
        
        $order = $orderCollection->getFirstItem();
        $validationHours = Mage::getStoreConfig('payment/klikpay/order_validation');
        $validationOption = Mage::getStoreConfig('payment/klikpay/order_validation_option');

        if ($order->getId()) {
            //checking order validate order
            if ($validationHours) {
                $orderCreatedDate = $order->getCreatedAt();
                $orderCreatedDate = date('Y-m-d H:i:s', strtotime($orderCreatedDate . ' +' . $validationHours . ' ' . $validationOption));
                $now = now();
                
                if (strtotime($now) > strtotime($orderCreatedDate)) {
                    $contentLog = sprintf("%s | order is not valid.", $transactionNo);
                    $this->writeLog($this->_typeTransaction, 'inquiry', $contentLog);
                    die ('order is not valid.');
                }
            }
            
            //checking signature
            if ($signature != $order->getKlikpaySignature()) {
                $contentLog = sprintf("%s | signature is not valid.", $transactionNo);
                $this->writeLog($this->_typeTransaction, 'inquiry', $contentLog);
                die ('signature is not valid.');
            }

            $items = $order->getAllItems();
            
            if (count($items) > 0) {
                header('Content-type: text/xml; charset=utf-8');    
                
                $xml  = '<?xml version="1.0" encoding="utf-8"?>';
                $xml .= '<OutputListTransactionIPAY>';
                $xml .= "<klikPayCode>" . $klikpayUserId . "</klikPayCode>";
                $xml .= "<transactionNo>" . $transactionNo . "</transactionNo>";
                $xml .= "<currency>IDR</currency>";
                
                foreach ($items as $itemId => $item) {
                    $_installmentTypeValue = Mage::helper('paymethod/klikpay')->getInstallmentOption($item->getInstallmentType(), 'value');
                    $_tenor = Mage::helper('paymethod/klikpay')->getInstallmentOption($item->getInstallmentType(), 'tenor');
                    $_merchantId = Mage::helper('paymethod/klikpay')->getInstallmentOption($item->getInstallmentType(), 'merchantid');
                    break;
                }

                if ($_installmentTypeValue == Bilna_Paymethod_Model_Method_Klikpay::PAYMENT_TYPE_FULL_TRANSACTION) {
                    $xml .= "<fullTransaction>";
                    $xml .= "<amount>" . number_format((int) $order->getGrandTotal(), 2, null, '') . "</amount>";
                    $xml .= "<description>" . Mage::helper('paymethod/klikpay')->_removeSymbols('Order Item ' . $transactionNo) . "</description>";
                    $xml .= "</fullTransaction>";
                }
                else {
                    $xml .= "<installmentTransaction>";
                    $xml .= "<itemName>" . Mage::helper('paymethod/klikpay')->_removeSymbols('Order Item ' . $transactionNo) . "</itemName>";
                    $xml .= "<quantity>1</quantity>";
                    $xml .= "<amount>" . number_format((int) $order->getGrandTotal(), 2, null, '') . "</amount>";
                    $xml .= "<tenor>" . $_tenor . "</tenor>";
                    $xml .= "<codePlan>000</codePlan>";
                    $xml .= "<merchantId>" . $_merchantId . "</merchantId>";
                    $xml .= "</installmentTransaction>";
                }
                
                $xml .= "<miscFee></miscFee>";
                $xml .= "<additionalData>" . $additionalData . "</additionalData>";
                $xml .= "</OutputListTransactionIPAY>";
                
                $contentLog = sprintf("%s | response_klikpay: %s", $transactionNo, $xml);
                $this->writeLog($this->_typeTransaction, 'inquiry', $contentLog);
                die ($xml);
            }
        }
        
        die();
    }
    
    public function paymentAction() {
        $this->writeLog($this->_typeTransaction, 'payment', 'request_method: ' . $this->getRequestMethod());
        
        $klikpayUserId = $this->getRequestData('klikPayCode');
        $transactionDate = $this->getRequestData('transactionDate');
        $transactionNo = $this->getRequestData('transactionNo');
        $currency = $this->getRequestData('currency');
        $currencyIDR = 'IDR';
        $currencyCheck = (!empty ($currency) && $currency == $currencyIDR) ? true : false;
        $transactionAmount = str_replace($currencyIDR, '', $this->getRequestData('totalAmount'));
        $payType = $this->getRequestData('payType');
        $approvalCode = $this->getRequestData('approvalCode');
        $authKey = $this->getRequestData('authKey');
        $additionalData = $this->getRequestData('additionalData');
        $status = '01';
        
        $contentLog = sprintf("%s | request_klikpay: %s", $transactionNo, json_encode($this->getRequestData()));
        $this->writeLog($this->_typeTransaction, 'payment', $contentLog);
        
        $idReason = '';
        $enReason = '';
        $idReasonTrxSuccess = 'Sukses.';
        $enReasonTrxSuccess = 'Success.';
        $idReasonTrxFailed = 'Transaksi anda tidak dapat diproses.';
        $enReasonTrxFailed = 'Your transaction cannot be processed.';
        $idReasonTrxPaid = 'Transaksi anda telah dibayar.';
        $enReasonTrxPaid = 'Your transaction has been paid.';
        $idReasonTrxExpired = 'Transaksi anda telah kedaluwarsa.';
        $enReasonTrxExpired = 'Your transaction has expired.';
        
        if ($currencyCheck === true) {
            $orderCollection = Mage::getModel('sales/order')->getCollection();
            $orderCollection->addFieldToFilter('increment_id', array ('eq' => $transactionNo));
            $orderCollection->getSelect()->join(array ('SFOP' => 'sales_flat_order_payment'), 'SFOP.method = "klikpay" AND main_table.entity_id = SFOP.parent_id', array ('method'), null, 'left');

            $order = $orderCollection->getFirstItem();
            $validationHours = Mage::getStoreConfig('payment/klikpay/order_validation');
            $validationOption = Mage::getStoreConfig('payment/klikpay/order_validation_option');
            $clearKey = Mage::getStoreConfig('payment/klikpay/klikpay_clearkey');
            $validHours = 'yes';
            $authKeyDate = date('d/m/Y H:i:s', strtotime($order->getCreatedAt()));
            $oauthkey = Mage::helper('paymethod/klikpay')->authkey($klikpayUserId, $transactionNo, $currency, $authKeyDate, $clearKey);

            if ($validationHours) {
                $orderCreatedDate = $order->getCreatedAt();
                $orderCreatedDate = date('Y-m-d H:i:s', strtotime($orderCreatedDate . ' +' . $validationHours . ' ' . $validationOption));
                $now = now();

                if (strtotime($now) > strtotime($orderCreatedDate)) {
                    $validHours = 'no';
                }
            }

            if ($order->getId()) {
                if (number_format((int) $order->getGrandTotal(), 2, null, '') != $transactionAmount) {
                    $idReason = $idReasonTrxFailed;
                    $enReason = $enReasonTrxFailed;
                    $this->writeLog($this->_typeTransaction, 'payment', sprintf("%s | %s", $transactionNo, 'Amount failed.'));
                }
                else if ($order->getStatus() == 'processing') {
                    $idReason = $idReasonTrxPaid;
                    $enReason = $enReasonTrxPaid;
                    $this->writeLog($this->_typeTransaction, 'payment', sprintf("%s | %s", $transactionNo, $enReason));
                }
                else if ($validHours == 'no') {
                    $idReason = $idReasonTrxExpired;
                    $enReason = $enReasonTrxExpired;
                    $this->writeLog($this->_typeTransaction, 'payment', sprintf("%s | %s", $transactionNo, $enReason));
                }
                else if ($authKey != $oauthkey) {
                    $idReason = $idReasonTrxFailed;
                    $enReason = $enReasonTrxFailed;
                    $this->writeLog($this->_typeTransaction, 'payment', sprintf("%s | %s (%s)", $transactionNo, 'AuthKey Failed.', $oauthkey));
                }
                else {
                    $transactionAmount = number_format((int) $transactionAmount, 2, null, '');
                    $status = '00';
                    
                    $idReason = $idReasonTrxSuccess;
                    $enReason = $enReasonTrxSuccess;

                    // create invoice
                    try {
                        if ($order->canInvoice()) {
                            $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();

                            if ($invoice->getTotalQty()) {
                                $invoice->setGrandTotal($order->getGrandTotal());
                                $invoice->setBaseGrandTotal($order->getBaseGrandTotal());
                                $invoice->register();
                                $transactionSave = Mage::getModel('core/resource_transaction')
                                    ->addObject($invoice)
                                    ->addObject($invoice->getOrder());
                                $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true)->save();
                                $transactionSave->save();
                                $invoice->sendEmail(true, '');
                            }
                            else {
                                $status = '01';
                                $idReason = $idReasonTrxFailed;
                                $enReason = $enReasonTrxFailed;
                                $this->writeLog($this->_typeTransaction, 'payment', sprintf("%s | %s", $transactionNo, 'Get Invoice Total Qty failed.'));
                            }
                        }
                        else {
                            $this->_status = '01';
                            $idReason = $idReasonTrxFailed;
                            $enReason = $enReasonTrxFailed;
                            $this->writeLog($this->_typeTransaction, 'payment', sprintf("%s | %s", $transactionNo, 'Cannot create an invoice.'));
                        }
                    }
                    catch (Mage_Core_Exception $e) {
                        $this->_status = '01';
                        $idReason = $idReasonTrxFailed;
                        $enReason = $enReasonTrxFailed;
                        $this->writeLog($this->_typeTransaction, 'payment', sprintf("%s | %s", $transactionNo, $e->getMessage()));
                    }
                }
            }
            else {
                $idReason = $idReasonTrxFailed;
                $enReason = $enReasonTrxFailed;
                $this->writeLog($this->_typeTransaction, 'payment', sprintf("%s | %s", $transactionNo, 'Order failed.'));
            }
        }
        else {
            $this->_status = '01';
            $idReason = $idReasonTrxFailed;
            $enReason = $enReasonTrxFailed;
            $this->writeLog($this->_typeTransaction, 'payment', sprintf("%s | %s", $transactionNo, 'Currency is not IDR.'));
        }
        
        header('Content-type: text/xml; charset=utf-8');   
        $xml  ='<?xml version="1.0" encoding="utf-8"?>';
        $xml .= "<OutputPaymentIPAY>";
        $xml .= "<klikPayCode>" . $klikpayUserId . "</klikPayCode>";
        $xml .= "<transactionNo>" . $transactionNo . "</transactionNo>";
        $xml .= "<transactionDate>" . $transactionDate . "</transactionDate>";
        $xml .= "<currency>" . $currency . "</currency>";
        $xml .= "<totalAmount>" . $transactionAmount . "</totalAmount>";
        $xml .= "<payType>" . $payType . "</payType>";
        $xml .= "<approvalCode>";
        $xml .= "<fullTransaction>00002</fullTransaction>";
        $xml .= "<installmentTransaction>00003</installmentTransaction>";
        $xml .= "</approvalCode>";
        $xml .= "<status>" . $status . "</status>";
        $xml .= "<reason>";
        $xml .= "<indonesian>" . $idReason . "</indonesian>";
        $xml .= "<english>" . $enReason . "</english>";
        $xml .= "</reason>";
        $xml .= "<additionalData>" . $additionalData . "</additionalData>";            
        $xml .= "</OutputPaymentIPAY>";                 

        $contentLog = sprintf("%s | response_klikpay: %s", $transactionNo, $xml);
        $this->writeLog($this->_typeTransaction, 'payment', $contentLog);
        die($xml);
    }
    
    public function thankyouAction() {
        // parameter id tidak ada
        if (!$this->getRequest()->getParam('id')) {
            $this->_redirect('checkout/cart');
        }

        $this->loadLayout();
        $this->getLayout()->getBlock('head')->setTitle($this->__('Thank you Page'));
        $this->renderLayout();
    }
    
    public function payAction() {
        $this->loadLayout();
        $this->renderLayout();
    }
    
    protected function getRequestMethod() {
        return Mage::getStoreConfig('payment/' . $this->_code . '/request_method');
    }
    
    protected function getRequestData($key = '') {
        $method = $this->getRequestMethod();
        $result = '';
        
        if ($method == 'POST') {
            if ($key == '') {
                $result = $this->getRequest()->getPost();
            }
            else {
                if ($this->getRequest()->getPost($key)) {
                    $result = $this->getRequest()->getPost($key);
                }
            }
        }
        else {
            if ($key == '') {
                $result = $this->getRequest()->getParams();
            }
            else {
                if ($this->getRequest()->getParam($key)) {
                    $result = $this->getRequest()->getParam($key);
                }
            }
        }
        
        return $result;
    }

    protected function writeLog($type, $logFile, $content) {
        $tdate = date('Ymd', Mage::getModel('core/date')->timestamp(time()));
        $filename = sprintf("%s_%s.%s", $this->_code, $logFile, $tdate);
        
        return Mage::helper('paymethod')->writeLogFile($this->_code, $type, $filename, $content);
    }
}
