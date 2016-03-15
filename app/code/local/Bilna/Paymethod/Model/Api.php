<?php
/**
 * API2 class for payment gateway (admin)
 *
 * @category   Bilna
 * @package    Bilna_Paymethod
 * @author     Development Team <development@bilna.com>
 */

class Bilna_Paymethod_Model_Api {
    public function creditcardCharge($order, $_tokenId = null) {
        Mage::helper('paymethod')->loadVeritransNamespace();

        // setting config vtdirect
        $vtdirectConfig = $this->getVtdirectConfig();
        Veritrans_Config::$serverKey = $this->getVtdirectServerKey($vtdirectConfig);
        Veritrans_Config::$isProduction = $this->getVtdirectIsProduction($vtdirectConfig);

        $incrementId = $order->getIncrementId();
        $tokenId = is_null($_tokenId) ? $this->getTokenId() : $_tokenId;
        $grossAmount = $order->getGrandTotal();
        $paymentType = 'credit_card'; //- hardcode
        $typeTransaction = 'transaction'; //- hardcode

        // Optional
        //$billingAddress = $this->getBillingAddress();
        //$shippingAddress = $this->getShippingAddress();

        $paymentCode = $order->getPayment()->getMethodInstance()->getCode();
        $bankConfig = $this->getBankConfig($paymentCode);
        $acquiredBank = $this->getAcquiredBank($bankConfig, $grossAmount);

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
        $transactionData['credit_card']['bins'] = $this->getBins($order);

        $installmentProcess = $this->getInstallmentProcess($bankConfig);

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
            $this->writeLog($paymentCode, $typeTransaction, 'charge', 'request: ' . json_encode($transactionData));
            $response = Veritrans_VtDirect::charge($transactionData);
            $this->writeLog($paymentCode, $this->_typeTransaction, 'charge', 'response: ' . json_encode($response));
        }
        catch (Exception $e) {
            $this->writeLog($paymentCode, $typeTransaction, 'charge', "error: [" . $incrementId . "] " . $e->getMessage());
            $responseArr = array (
                'order_id' => $incrementId,
                'transaction_status' => 'deny',
                'fraud_status' => 'deny',
                'status_message' => $e->getMessage(),
                'bank' => $acquiredBank
            );
            $response = (object) $responseArr;
        }
        
        $result = array (
            'order_no' => $incrementId,
            'request' => $transactionData,
            'response' => $response,
            'type' => 'C',
        );

        return $result;
    }
    
    public function getVtdirectConfig() {
        return Mage::getStoreConfig('payment/vtdirect');
    }
    
    public function getVtdirectServerKey($vtdirectConfig = null) {
        if (is_null($vtdirectConfig)) {
            $vtdirectConfig = $this->getVtdirectConfig();
        }
        
        return $vtdirectConfig['server_key'];
    }
    
    public function getVtdirectIsProduction($vtdirectConfig = null) {
        if (is_null($vtdirectConfig)) {
            $vtdirectConfig = $this->getVtdirectConfig();
        }
        
        $isDevelopmentTesting = $vtdirectConfig['development_testing'];
        
        if ($isDevelopmentTesting) {
            return false;
        }

        return true;
    }
    
    public function getTokenId() {
        $tokenId = Mage::getSingleton('core/session')->getVtdirectTokenId();

        /**
         * remove token_id session
         */
        Mage::getSingleton("core/session")->unsVtdirectTokenIdCreate();
        Mage::getSingleton("core/session")->unsVtdirectTokenId();

        return $tokenId;
    }
    
    public function getBankConfig($paymentCode) {
        return Mage::getStoreConfig('payment/' . $paymentCode);
    }

    public function getAcquiredBank($bankConfig, $grossAmount) {
        $result = $bankConfig['bank_acquired'];

        if ($bankConfig['threedsecure']) {
            if ($grossAmount >= $bankConfig['threedsecure_min_order_total']) {
                $result = $bankConfig['threedsecure_bank_acquired'];
            }
        }

        return $result;
    }
    
    public function getBins($order) {
        $digit = 6;
        $result = substr($order->getPayment()->getCcBins(), 0, $digit);

        return array ($result);
    }
    
    public function getInstallmentProcess($bankConfig) {
        return $bankConfig['installment_process'];
    }
    
    public function getInstallment($items) {
        foreach ($items as $itemId => $item) {
            $installmentType = $item->getInstallmentType();

            if ($installmentType > 1) {
                return $installmentType;
            }
        }

        return false;
    }
    
    protected function writeLog($paymentCode, $type, $logFile, $content) {
        $tdate = date('Ymd', Mage::getModel('core/date')->timestamp(time()));
        $filename = sprintf("%s_%s.%s", $paymentCode, $logFile, $tdate);
        $content = "[" . gethostname() . "] " . $content;

        return Mage::helper('paymethod')->writeLogFile($paymentCode, $type, $filename, $content);
    }
}
