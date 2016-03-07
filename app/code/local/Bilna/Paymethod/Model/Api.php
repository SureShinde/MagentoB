<?php
/**
 * API2 class for payment gateway (admin)
 *
 * @category   Bilna
 * @package    Bilna_Paymethod
 * @author     Development Team <development@bilna.com>
 */

class Bilna_Paymethod_Model_Api {
	protected $_typeTransaction = 'transaction';

    protected function getVtdirectServerKey() {
        return Mage::getStoreConfig('payment/vtdirect/server_key');
    }
    
    protected function getVtdirectIsProduction() {
        $isProduction = Mage::getStoreConfig('payment/vtdirect/development_testing');
        
        if ($isProduction) {
            return false;
        }
        
        return true;
    }

    protected function getAcquiredBank($paymentCode) {
        return Mage::getStoreConfig('payment/' . $paymentCode . '/bank_acquired');
    }

    protected function getBins($order, $paymentCode) {
        $digit = 6;
        //$digit = ($paymentCode == 'othervisa' || $paymentCode == 'othermc') ? 1 : 6;
        $result = substr($order->getPayment()->getCcBins(), 0, $digit);
        
        return array ($result);
    }

    protected function getInstallment($items) {
        foreach ($items as $itemId => $item) {
            $installmentType = $item->getInstallmentType();
            
            if ($installmentType > 1) {
                return $installmentType;
            }
        }
        
        return false;
    }

    protected function getInstallmentProcess($paymentCode) {
        return Mage::getStoreConfig('payment/' . $paymentCode . '/installment_process');
    }

    protected function logProgress($message) {
        Mage::log($message, null, 'newstack.log');
    }

    public function creditcardCharge($order, $tokenId) {
        if (empty ($tokenId)) {
            return false;
        }
        
        Mage::helper('paymethod')->loadVeritransNamespace();

        // setting config vtdirect
        Veritrans_Config::$serverKey = $this->getVtdirectServerKey();
        Veritrans_Config::$isProduction = $this->getVtdirectIsProduction();

        $incrementId = $order->getIncrementId();
        $grossAmount = $order->getGrandTotal();
        $paymentType = 'credit_card'; //hardcode

        $paymentCode = $order->getPayment()->getMethodInstance()->getCode();
        $acquiredBank = $this->getAcquiredBank($paymentCode);

        // Required
        $customerDetails = array (
            'first_name' => $order->getBillingAddress()->getFirstname(),
            'last_name' => $order->getBillingAddress()->getLastname(),
            'email' => $order->getBillingAddress()->getEmail(),
            'phone' => $order->getBillingAddress()->getTelephone(),
            //'billing_address' => $billingAddress,
            //'shipping_address' => $shippingAddress
        );
        $transactionDetails = array (
            'order_id' => $incrementId,
            'gross_amount' => $grossAmount
        );

        //Data that will be sent to request charge transaction with credit card.
        $transactionData = array ();
        $transactionData['payment_type'] = $paymentType;
        $transactionData['credit_card']['token_id'] = $tokenId;
        $transactionData['credit_card']['bank'] = $acquiredBank;
        $transactionData['credit_card']['bins'] = $this->getBins($order, $paymentCode);

        $installmentProcess = $this->getInstallmentProcess($paymentCode);
        
        if ($installmentProcess != 'manual') {
            $items = $order->getAllItems();
            $installmentId = $this->getInstallment($items);
            $this->logProgress('installmentTerm: ' . $installmentId);

            if ($installmentId) {
                $transactionData['credit_card']['installment_term'] = $installmentId;
            }
        }

        $transactionData['transaction_details'] = $transactionDetails;
        $transactionData['customer_details'] = $customerDetails;

        try {
            $this->writeLog($paymentCode, $this->_typeTransaction, 'charge', 'request: ' . json_encode($transactionData));
            $result = Veritrans_VtDirect::charge($transactionData);
            $this->writeLog($paymentCode, $this->_typeTransaction, 'charge', 'response: ' . json_encode($result));
        }
        catch (Exception $e) {
        	$this->writeLog($paymentCode, $this->_typeTransaction, 'charge', "error: [" . $incrementId . "] " . $e->getMessage());
            $response = array (
                'transaction_status' => 'deny',
                'fraud_status' => 'deny',
                'status_message' => $e->getMessage(),
                'bank' => $acquiredBank
            );
            $result = (object) $response;
        }
        
        return $result;
	}

    public function vtdirectRedirectCharge($order) {
        Mage::helper('paymethod')->loadVeritransNamespace();
        
        //- setting config vtdirect
        Veritrans_Config::$serverKey = $this->getVtdirectServerKey();
        Veritrans_Config::$isProduction = $this->getVtdirectIsProduction();
        
        $incrementId = $order->getIncrementId();
        $grossAmount = $order->getGrandTotal();
        $paymentCode = $order->getPayment()->getMethodInstance()->getCode();
        $paymentType = Mage::getStoreConfig('payment/' . $paymentCode . '/vtdirect_payment_type');
        
        //-Required
        $transactionDetails = array (
            'order_id' => $incrementId,
            'gross_amount' => $grossAmount
        );
        $customerDetails = array (
            'first_name' => $order->getBillingAddress()->getFirstname(),
            'last_name' => $order->getBillingAddress()->getLastname(),
            'email' => $this->getCustomerEmail($order->getBillingAddress()->getEmail()),
            'phone' => $order->getBillingAddress()->getTelephone(),
        );
        
        //- Data that will be sent for charge transaction request with Mandiri E-cash.
        $transactionData = array (
            'payment_type' => $paymentType,
            'transaction_details' => $transactionDetails,
            'customer_details' => $customerDetails,
            'mandiri_ecash' => array (
                'description' => 'Transaction Description Mandiri E-Cash Bilna.com',
            ),
        );
        
        try {
            $this->writeLog($paymentCode, $this->_typeTransaction, 'charge', 'request: ' . json_encode($transactionData));
            $result = Veritrans_VtDirect::charge($transactionData);
            $this->writeLog($paymentCode, $this->_typeTransaction, 'charge', 'response: ' . json_encode($result));
        }
        catch (Exception $e) {
            $this->writeLog($paymentCode, $this->_typeTransaction, 'charge', "error: [" . $incrementId . "] " . $e->getMessage());
            $response = array (
                'transaction_status' => 'deny',
                'fraud_status' => 'deny',
                'status_message' => $e->getMessage()
            );
            $result = (object) $response;
        }
        
        return $result;
    }

    protected function getCustomerEmail($email) {
        //if (Mage::getStoreConfig('payment/vtdirect/development_testing')) {
        //    return 'vt-testing@veritrans.co.id';
        //}
        
        return $email;
    }

    protected function writeLog($paymentCode, $type, $logFile, $content) {
        $tdate = date('Ymd', Mage::getModel('core/date')->timestamp(time()));
        $filename = sprintf("%s_%s.%s", $paymentCode, $logFile, $tdate);
        $content = "[" . gethostname() . "] " . $content;
        
        return Mage::helper('paymethod')->writeLogFile($paymentCode, $type, $filename, $content);
    }
}
