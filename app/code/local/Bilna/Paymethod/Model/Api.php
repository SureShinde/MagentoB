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

        // Optional
        //$billingAddress = $this->getBillingAddress();
        //$shippingAddress = $this->getShippingAddress();

        $paymentCode = $order->getPayment()->getMethodInstance()->getCode();
        $bankConfig = $this->getPaymentConfig($paymentCode);
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

        if ($allowInstallment = $this->getAllowInstallment($bankConfig)) {
            $items = $order->getAllItems();
            $installmentTenor = $this->getInstallment($items);

            if ($installmentTenor) {
                $transactionData['credit_card']['installment_term'] = $installmentTenor;
            }
        }

        $transactionData['transaction_details'] = $transactionDetails;
        $transactionData['customer_details'] = $customerDetails;
        
        try {
            $this->writeLog($paymentCode, $this->_typeTransaction, 'charge', 'request: ' . json_encode($transactionData));
            $response = Veritrans_VtDirect::charge($transactionData);
            $this->writeLog($paymentCode, $this->_typeTransaction, 'charge', 'response: ' . json_encode($response));
        }
        catch (Exception $e) {
            $this->writeLog($paymentCode, $this->_typeTransaction, 'charge', "error: [" . $incrementId . "] " . $e->getMessage());
            $responseArr = array (
                'order_id' => $incrementId,
                'transaction_status' => 'deny',
                'fraud_status' => 'deny',
                'status_message' => $e->getMessage(),
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
    
    public function vtdirectRedirectCharge($order) {
        Mage::helper('paymethod')->loadVeritransNamespace();

        //- setting config vtdirect
        Veritrans_Config::$serverKey = $this->getVtdirectServerKey();
        Veritrans_Config::$isProduction = $this->getVtdirectIsProduction();

        $incrementId = $order->getIncrementId();
        $grossAmount = $order->getGrandTotal();
        $paymentCode = $order->getPayment()->getMethodInstance()->getCode();
        $paymentConfig = $this->getPaymentConfig($paymentCode);
        $paymentType = $paymentConfig['vtdirect_payment_type'];

        //-Required
        $transactionDetails = array (
            'order_id' => $incrementId,
            'gross_amount' => $grossAmount
        );
        $customerDetails = array (
            'first_name' => $order->getBillingAddress()->getFirstname(),
            'last_name' => $order->getBillingAddress()->getLastname(),
            'email' => $order->getBillingAddress()->getEmail(),
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
            $response = Veritrans_VtDirect::charge($transactionData);
            $this->writeLog($paymentCode, $this->_typeTransaction, 'charge', 'response: ' . json_encode($response));
        }
        catch (Exception $e) {
            $this->writeLog($paymentCode, $this->_typeTransaction, 'charge', "error: [" . $incrementId . "] " . $e->getMessage());
            $responseArr = array (
                'order_id' => $incrementId,
                'transaction_status' => 'deny',
                'fraud_status' => 'deny',
                'status_message' => $e->getMessage(),
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
    
    public function vtdirectVaCharge($order) {
        Mage::helper('paymethod')->loadVeritransNamespace();

        //- setting config vtdirect
        Veritrans_Config::$serverKey = $this->getVtdirectServerKey();
        Veritrans_Config::$isProduction = $this->getVtdirectIsProduction();

        $incrementId = $order->getIncrementId();
        $grossAmount = $order->getGrandTotal();
        $orderPayment = $order->getPayment();
        $paymentCode = $orderPayment->getMethodInstance()->getCode();
        $paymentConfig = $this->getPaymentConfig($paymentCode);
        $paymentType = $paymentConfig['vtdirect_payment_type'];
        $paymentBank = $paymentConfig['bank'];
        $expiryDuration = $paymentConfig['expiry_duration'];
        $expiryDurationUnit = $paymentConfig['expiry_duration_unit'];
        
        $inquiryTextID = $paymentConfig['inquiry_text_indonesian'];
        $inquiryTextEN = $paymentConfig['inquiry_text_english'];
        $paymentTextID = $paymentConfig['payment_text_indonesian'];
        $paymentTextEN = $paymentConfig['payment_text_english'];

        $inquiryRowLimit = 5;
        $paymentRowLimit = 9;
        $maxTextLength = 38;

        // BEGIN - Replace freetext containing defined variables 
        $stringToReplaceArr = ['{{order_no}}' => $incrementId];
        
        foreach ($stringToReplaceArr as $stringToReplace => $replacementText) {
            $inquiryTextID = str_replace($stringToReplace, $replacementText, $inquiryTextID);
            $inquiryTextEN = str_replace($stringToReplace, $replacementText, $inquiryTextEN);
            $paymentTextID = str_replace($stringToReplace, $replacementText, $paymentTextID);
            $paymentTextEN = str_replace($stringToReplace, $replacementText, $paymentTextEN);
        }
        // END - Replace freetext containing defined variables 

        $inquiryTextIDArr = $this->_parseFreeText($inquiryTextID, 'id', $inquiryRowLimit, $maxTextLength);
        $inquiryTextENArr = $this->_parseFreeText($inquiryTextEN, 'en', $inquiryRowLimit, $maxTextLength);
        $paymentTextIDArr = $this->_parseFreeText($paymentTextID, 'id', $paymentRowLimit, $maxTextLength);
        $paymentTextENArr = $this->_parseFreeText($paymentTextEN, 'en', $paymentRowLimit, $maxTextLength);
        
        // Building Inquiry Text Data
        $inquiryTextArr = [];
        
        // Loop though one array
        foreach ($inquiryTextIDArr as $key => $val) {
            if (isset ($inquiryTextENArr[$key])) {
                $val2 = $inquiryTextENArr[$key]; // Get the values from the other array
                $inquiryTextArr[$key] = $val + $val2; // combine 'em
            }
            else {
                $inquiryTextArr[$key] = $val;
            }
        }
        
        // Building Payment Text Data
        $paymentTextArr = [];
        
        // Loop though one array
        foreach ($paymentTextIDArr as $key => $val) {
            if (isset ($paymentTextENArr[$key])) {
                $val2 = $paymentTextENArr[$key]; // Get the values from the other array
                $paymentTextArr[$key] = $val + $val2; // combine 'em
            }
            else {
                $paymentTextArr[$key] = $val;
            }
        }

        //- Required
        $transactionDetails = [
            'order_id' => $incrementId,
            'gross_amount' => $grossAmount
        ];
        $customerDetails = [
            'first_name' => $order->getBillingAddress()->getFirstname(),
            'last_name' => $order->getBillingAddress()->getLastname(),
            'email' => $order->getBillingAddress()->getEmail(),
            'phone' => $order->getBillingAddress()->getTelephone(),
        ];
        
        //- Data that will be sent for charge transaction request with Virtual Account.
        $transactionData = [
            'payment_type' => $paymentType,
            'transaction_details' => $transactionDetails,
            'customer_details' => $customerDetails,
            $paymentType => [
                'bank' => $paymentBank,
                'free_text' => [
                    'inquiry' => $inquiryTextArr,
                    'payment' => $paymentTextArr
                ],
            ],
        ];

        //- Data that will be sent for charge transaction request with Mandiri E-cash.
        $transactionData = [
            'payment_type' => $paymentType,
            'transaction_details' => $transactionDetails,
            'customer_details' => $customerDetails,
            $paymentType => [
                'bank' => $paymentBank,
            ],
        ];
        
        // If custom expiry is set, add custom expiry to the request
        if (!empty ($expiryDuration)) {
            $transactionData['custom_expiry'] = [
                'expiry_duration' => $expiryDuration,
                'unit' => $expiryDurationUnit
            ];
        }

        try {
            $this->writeLog($paymentCode, $this->_typeTransaction, 'charge', 'request: ' . json_encode($transactionData));
            $response = Veritrans_VtDirect::charge($transactionData);
            $this->setOrderPaymentVaNumber($orderPayment, $response);
            $this->writeLog($paymentCode, $this->_typeTransaction, 'charge', 'response: ' . json_encode($response));
        }
        catch (Exception $e) {
            $this->writeLog($paymentCode, $this->_typeTransaction, 'charge', "error: [" . $incrementId . "] " . $e->getMessage());
            $responseArr = [
                'order_id' => $incrementId,
                'transaction_status' => 'deny',
                'fraud_status' => 'deny',
                'status_message' => $e->getMessage(),
            ];
            $response = (object) $responseArr;
        }

        $result = [
            'order_no' => $incrementId,
            'request' => $transactionData,
            'response' => $response,
            'type' => 'C',
        ];

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
    
    public function getPaymentConfig($paymentCode) {
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
    
    public function getAllowInstallment($bankConfig) {
        return $bankConfig['allow_installment'];
    }

    public function getInstallmentProcess($bankConfig) {
        return $bankConfig['installment_process'];
    }
    
    public function getInstallment($items) {
        foreach ($items as $itemId => $item) {
            if ($item->getInstallment()) {
                $installmentType = $item->getInstallmentType();

                if ($installmentType > 1) {
                    return $installmentType;
                }
            }
        }

        return false;
    }
    
    public function setOrderPaymentVaNumber($orderPayment, $response) {
        $vaNumbers = $response->va_numbers[0];
        $orderPayment->setVaNumber($vaNumbers->va_number); //- set value of va_number field from table sales_order_flat_payment
        $orderPayment->save();
    }
    
    /**
     * Function to parse text for Veritrans transaction
     * @param $textToParse Array of text
     * @param $languageKey string Language Key that will be used in the index
     * @param $rowLimit int Limit of the return rows
     * @param $maxRowLength int Maximum length of each row returned
     * @return array of parsed text
     */
    protected function _parseFreeText($textToParse, $languageKey='id', $rowLimit = 5, $maxRowLength = 38) {
        $parsedText = [];
        $splittedText = explode(PHP_EOL, $textToParse); 
        $index = 0;

        $numRow = count($splittedText);
        
        if ($numRow > $rowLimit) {
            $numRow = $rowLimit;
        }

        if (!empty ($splittedText)) {
            for ($i = 0; $i < $numRow; $i++) {
                if (!empty ($splittedText[$i])) {
                    $parsedText[$index][$languageKey] = substr(trim($splittedText[$i]), 0, $maxRowLength);
                    $index++;
                }
            }
        }
        
        return $parsedText;
    }

    protected function writeLog($paymentCode, $type, $logFile, $content) {
        $tdate = date('Ymd', Mage::getModel('core/date')->timestamp(time()));
        $filename = sprintf("%s_%s.%s", $paymentCode, $logFile, $tdate);
        $content = "[" . gethostname() . "] " . $content;

        return Mage::helper('paymethod')->writeLogFile($paymentCode, $type, $filename, $content);
    }
}
