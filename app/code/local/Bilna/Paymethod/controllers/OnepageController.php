<?php
/**
 * Description of Bilna_Paymethod_OnepageController
 *
 * @author Bilna Development Team <development@bilna.com>
 */

require_once 'Mage/Checkout/controllers/OnepageController.php';

class Bilna_Paymethod_OnepageController extends Mage_Checkout_OnepageController {
    protected $_payType = '';
    protected $_typeTransaction = 'transaction';

    /**
     * @override
     * Checkout page
     */
    public function indexAction()
    {
        if (!Mage::helper('checkout')->canOnepageCheckout()) {
            Mage::getSingleton('checkout/session')->addError($this->__('The onepage checkout is disabled.'));
            $this->_redirect('checkout/cart');
            return;
        }
        $quote = $this->getOnepage()->getQuote();
        if (!$quote->hasItems() || $quote->getHasError()) {
            $this->_redirect('checkout/cart');
            return;
        }
        if (!$quote->validateMinimumAmount()) {
            $error = Mage::getStoreConfig('sales/minimum_order/error_message') ?
                Mage::getStoreConfig('sales/minimum_order/error_message') :
                Mage::helper('checkout')->__('Subtotal must exceed minimum order amount');

            Mage::getSingleton('checkout/session')->addError($error);
            $this->_redirect('checkout/cart');
            return;
        }

        // BEGIN - Check if Cart is valid for cross border items
        $crossBorderModel = Mage::getModel('bilna_crossborder/CrossBorder');

        $validationResult = $crossBorderModel->validate();
        if ($validationResult['success'] == false) {
            $this->_redirect('checkout/cart');
        }
        // END - Check if Cart is valid for cross border items

        Mage::getSingleton('checkout/session')->setCartWasUpdated(false);
        Mage::getSingleton('customer/session')->setBeforeAuthUrl(Mage::getUrl('*/*/*', array('_secure' => true)));
        $this->getOnepage()->initCheckout();
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Checkout'));
        $this->renderLayout();
    }

    public function saveOrderAction() {
        if ($this->_expireAjax()) {
            return;
        }

        $paymentCode = Mage::getSingleton('checkout/session')->getQuote()->getPayment()->getMethodInstance()->getCode();
        $paymentSupportInstallment = explode(',', Mage::getStoreConfig('bilna_module/paymethod/payment_support_installment'));

        // set tokenid for credit card
        if (in_array($paymentCode, $this->getPaymentMethodCc())) {
            $tokenId = $this->getRequest()->getPost('token_id', false);

            Mage::getSingleton('core/session')->unsVtdirectTokenIdCreate();
            Mage::getSingleton('core/session')->unsVtdirectTokenId();
            Mage::getSingleton('core/session')->setVtdirectTokenIdCreate(date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(time())));
            Mage::getSingleton('core/session')->setVtdirectTokenId($tokenId);
        }

        if (in_array($paymentCode, $paymentSupportInstallment)) {
            $result = array ();

            try {
                /**
                 * installment
                 */
                if ($allowInstallment = $this->getRequest()->getPost('allow_installment', false)) {
                    //save installment options in quote item table
                    $installmentMethod = $this->getRequest()->getPost('installment_method', false);

                    if ($installmentTenor = $this->getRequest()->getPost('installment', false)) {
                        $quote_items = $this->getOnepage()->getQuote()->getAllItems();
                        $item_ids = array ();

                        foreach ($quote_items as $item) {
                            $item_ids[] = $item->getProductId();
                        }

                        $installmentOptionType = Mage::getStoreConfig('payment/' . $paymentCode . '/installment_option');

                        /**
                         * installment type is per order
                         */
                        if ($installmentOptionType == 2) {
                            if ($installmentTenor == '') {
                                $result['success'] = false;
                                $result['error'] = true;
                                $result['error_messages'] = $this->__('Please select an installment type before placing the order.');
                                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));

                                return;
                            }

                            if ($installmentTenor > 1) {
                                foreach ($quote_items as $item) {
                                    $item->setInstallment($allowInstallment);
                                    $item->setInstallmentMethod($installmentMethod);
                                    $item->setInstallmentType($installmentTenor);
                                    $item->save();
                                }
                            }

                            if ($installmentTenor == $this->getPaymentTypeTransaction($paymentCode, 'full')) {
                                $this->_payType = $this->getPaymentTypeTransaction($paymentCode, 'full');
                            }
                            else {
                                $this->_payType = $this->getPaymentTypeTransaction($paymentCode, 'installment');
                            }
                        }
                        else { //if installment type is per item
                            if (array_diff($item_ids, array_keys($installmentTenor))) {
                                $result['success'] = false;
                                $result['error'] = true;
                                $result['error_messages'] = $this->__('Please select an installment type before placing the order.');
                                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));

                                return;
                            }

                            foreach ($quote_items as $item) {
                                foreach ($installmentTenor as $_productid => $installmentvalue) {
                                    if ($item->getProductId() == $_productid) {
                                        $item->setInstallmentType($installmentvalue);
                                        $item->save();
                                    }
                                }
                            }

                            //save pay type
                            $count = 0;
                            $arrayCount = count($installmentTenor);

                            foreach ($installmentTenor as $value) {
                                if ($value == $this->getPaymentTypeTransaction($paymentCode, 'full')) {
                                    $count++;
                                }
                            }

                            if ($arrayCount == $count) {
                                $this->_payType = $this->getPaymentTypeTransaction($paymentCode, 'full');
                            }
                            else if ($count >= 1) {
                                $this->_payType = $this->getPaymentTypeTransaction($paymentCode, 'combine');
                            }
                            else {
                                $this->_payType = $this->getPaymentTypeTransaction($paymentCode, 'installment');
                            }
                        }

                        $this->getOnepage()->getQuote()->setPayType($this->_payType)->save();
                        $this->getOnepage()->getQuote()->setCcBins($this->getRequest()->getPost('cc_bins', false))->save();
                    }
                    else {
                        $result['success'] = false;
                        $result['error'] = true;
                        $result['error_messages'] = $this->__('Please select an installment type before placing the order.');
                        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));

                        return;
                    }
                }
                else {
                    $this->_payType = $this->getPaymentTypeTransaction($paymentCode, 'full');
                    $this->getOnepage()->getQuote()->setPayType($this->_payType)->save();
                    $this->getOnepage()->getQuote()->setCcBins($this->getRequest()->getPost('cc_bins', false))->save();
                }

                if ($requiredAgreements = Mage::helper('checkout')->getRequiredAgreementIds()) {
                    $postedAgreements = array_keys($this->getRequest()->getPost('agreement', array ()));

                    if ($diff = array_diff($requiredAgreements, $postedAgreements)) {
                        $result['success'] = false;
                        $result['error'] = true;
                        $result['error_messages'] = $this->__('Please agree to all the terms and conditions before placing the order.');
                        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));

                        return;
                    }
                }

                if ($data = $this->getRequest()->getPost('payment', false)) {
                    $this->getOnepage()->getQuote()->getPayment()->importData($data);
                }

                $this->getOnepage()->saveOrder();

                $redirectUrl = $this->getOnepage()->getCheckout()->getRedirectUrl();
                $result['success'] = true;
                $result['error'] = false;
            }
            catch (Mage_Payment_Model_Info_Exception $e) {
                $message = $e->getMessage();

                if (!empty ($message)) {
                    $result['error_messages'] = $message;
                }

                $result['goto_section'] = 'payment';
                $result['update_section'] = array (
                    'name' => 'payment-method',
                    'html' => $this->_getPaymentMethodsHtml()
                );

            }
            catch (Mage_Core_Exception $e) {
                Mage::logException($e);
                Mage::helper('checkout')->sendPaymentFailedEmail($this->getOnepage()->getQuote(), $e->getMessage());
                $result['success'] = false;
                $result['error'] = true;
                $result['error_messages'] = $e->getMessage();

                if ($gotoSection = $this->getOnepage()->getCheckout()->getGotoSection()) {
                    $result['goto_section'] = $gotoSection;
                    $this->getOnepage()->getCheckout()->setGotoSection(null);
                }

                if ($updateSection = $this->getOnepage()->getCheckout()->getUpdateSection()) {
                    if (isset ($this->_sectionUpdateFunctions[$updateSection])) {
                        $updateSectionFunction = $this->_sectionUpdateFunctions[$updateSection];
                        $result['update_section'] = array (
                            'name' => $updateSection,
                            'html' => $this->$updateSectionFunction()
                        );
                    }

                    $this->getOnepage()->getCheckout()->setUpdateSection(null);
                }
            }
            catch (Exception $e) {
                Mage::logException($e);
                Mage::helper('checkout')->sendPaymentFailedEmail($this->getOnepage()->getQuote(), $e->getMessage());
                $result['success'] = false;
                $result['error'] = true;
                $result['error_messages'] = $this->__('There was an error processing your order. Please contact us or try again later.');
            }

            $this->getOnepage()->getQuote()->save();

            if (isset ($redirectUrl)) {
                $result['redirect'] = $redirectUrl;
            }

            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
        else {
            parent::saveOrderAction();
        }
    }

    protected function getPaymentTypeTransaction($paymentCode, $type) {
        if ($paymentCode == 'klikpay') {
            if ($type == 'full') {
                return Bilna_Paymethod_Model_Method_Klikpay::PAYMENT_TYPE_FULL_TRANSACTION;
            }
            else if ($type == 'installment') {
                return Bilna_Paymethod_Model_Method_Klikpay::PAYMENT_TYPE_INSTALLMENT_TRANSACTION;
            }
            else {
                return Bilna_Paymethod_Model_Method_Klikpay::PAYMENT_TYPE_COMBINE_TRANSACTION;
            }
        }
        else if ($paymentCode == 'anzcc') {
            if ($type == 'full') {
                return Bilna_Anzcc_Model_Anzcc::PAYMENT_TYPE_FULL_TRANSACTION;
            }
            else if ($type == 'installment') {
                return Bilna_Anzcc_Model_Anzcc::PAYMENT_TYPE_INSTALLMENT_TRANSACTION;
            }
            else {
                return Bilna_Anzcc_Model_Anzcc::PAYMENT_TYPE_COMBINE_TRANSACTION;
            }
        }
        else {
            return '';
        }
    }

    /**
     * Save payment ajax action
     *
     * Sets either redirect or a JSON response
     */
    public function savePaymentAction() {
        if ($this->_expireAjax()) {
            return;
        }

        try {
            if (!$this->getRequest()->isPost()) {
                $this->_ajaxRedirectResponse();
                return;
            }

            $data = $this->getRequest()->getPost('payment', array ());
            $paymentHide = explode(',', Mage::getStoreConfig('bilna_module/paymethod/payment_hide'));

            /**
             * save parameter token_id to session
             */
            if ($data['method'] == 'vtdirect' || in_array($data['method'], $paymentHide)) {
                $dataCc = $this->getRequest()->getPost('payment', array ());
                $data = array (
                    'use_points' => array_key_exists('use_points', $data) ? $data['use_points'] : '',
                    'points_amount' => array_key_exists('points_amount', $data) ? $data['points_amount'] : '',
                    'method' => $dataCc['cc_bank'],
                    'cc_owner' => $dataCc['cc_owner'],
                    'cc_type' => $dataCc['cc_type'],
                    'cc_bank' => $dataCc['cc_bank'],
                    //'token_id' => $dataCc['token_id'],
                    'cc_number' => $dataCc['cc_number'],
                    'cc_exp_month' => $dataCc['cc_exp_month'],
                    'cc_exp_year' => $dataCc['cc_exp_year'],
                    'cc_cid' => $dataCc['cc_cid'],
                    'cc_zipcode' => $dataCc['cc_zipcode'],
                    'cc_bins' => $dataCc['cc_bins']
                );

                //Mage::getSingleton('core/session')->unsVtdirectTokenIdCreate();
                //Mage::getSingleton('core/session')->unsVtdirectTokenId();
                Mage::getSingleton('core/session')->unsVtdirectZipCode();
                //Mage::getSingleton('core/session')->setVtdirectTokenIdCreate(date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(time())));
                //Mage::getSingleton('core/session')->setVtdirectTokenId($data['token_id']);
                Mage::getSingleton('core/session')->setVtdirectZipCode($data['cc_zipcode']);
            }

            $result = $this->getOnepage()->savePayment($data);
            $redirectUrl = $this->getOnepage()->getQuote()->getPayment()->getCheckoutRedirectUrl();

            if (empty ($result['error']) && !$redirectUrl) {
                $this->loadLayout('checkout_onepage_review');
                $result['goto_section'] = 'review';
                $result['update_section'] = array (
                    'name' => 'review',
                    'html' => $this->_getReviewHtml()
                );
            }

            if ($redirectUrl) {
                $result['redirect'] = $redirectUrl;
            }
        }
        catch (Mage_Payment_Exception $e) {
            if ($e->getFields()) {
                $result['fields'] = $e->getFields();
            }

            $result['error'] = $e->getMessage();
        }
        catch (Mage_Core_Exception $e) {
            $result['error'] = $e->getMessage();
        }
        catch (Exception $e) {
            Mage::logException($e);
            $result['error'] = $this->__('Unable to set Payment Method.');
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function bankCheckAction() {
        $cardNo = $this->getRequest()->getPost('card_no');
        $response = array (
            'status' => false,
            'data' => array (),
            'message' => null
        );

        if (in_array ($cardNo[0], array (4,5))) {
            $bankCode = Mage::getModel('paymethod/method_vtdirect')->getBankCode($cardNo);
            $ccType = $this->getCcType($bankCode);

            $configBank = Mage::getStoreConfig('payment/' . $bankCode);
            $acquiredBank = $configBank['bank_acquired'];
            $secure = $configBank['threedsecure'];
            $installmentProcess = $configBank['installment_process'];

            $response['status'] = true;
            $response['data'] = array (
                'bank_code' => $bankCode,
                'cc_type' => $ccType,
                'acquired_bank' => $acquiredBank,
                'secure' => $secure,
                'secure_acquired_bank' => $secure ? $configBank['threedsecure_bank_acquired'] : $acquiredBank,
                'secure_min' => $secure ? (int) $configBank['threedsecure_min_order_total'] : 0,
                'installment_process' => $installmentProcess
            );
        }
        else {
            $response['message'] = 'Please enter a valid credit card number.';
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
    }

    protected function getCcType($bank) {
        //$bankArr = explode('_', $bank);
        $ccType = (strtoupper(substr($bank, -2)) == 'MC') ? 'MC' : 'VI';

        return $ccType;
    }

    public function successAction() {
        $canceled = 0;

        if ($this->getRequest()->getParam('order_no')) {
            $orderNo = $this->getRequest()->getParam('order_no');

            /**
             * Credit Card handling
             */
            $order = Mage::getModel('sales/order')->loadByIncrementId($orderNo);
            $lastOrderId = $order->getId();
            $paymentCode = $order->getPayment()->getMethodInstance()->getCode();

            // FDS (BILNA-1333) - Start
            $canceled = Mage::helper('bilna_fraud')->checkOrderStatus($orderNo, 1);
            // FDS (BILNA-1333) - End

            if (in_array($paymentCode, $this->getPaymentMethodCc())) {
                $this->_redirect('checkout/cart');
                return;
            }
        }
        else {
            $session = $this->getOnepage()->getCheckout();

            if (!$session->getLastSuccessQuoteId()) {
                $this->_redirect('checkout/cart');
                return;
            }

            $lastQuoteId = $session->getLastQuoteId();
            $lastOrderId = $session->getLastOrderId();

            // FDS (BILNA-1333) - Start
            $canceled = Mage::helper('bilna_fraud')->checkOrderStatus($lastOrderId, 0);
            // FDS (BILNA-1333) - End

            $lastRecurringProfiles = $session->getLastRecurringProfileIds();

            if (!$lastQuoteId || (!$lastOrderId && empty ($lastRecurringProfiles))) {
                $this->_redirect('checkout/cart');
                return;
            }

            $order = Mage::getModel('sales/order')->load($lastOrderId);
            $paymentCode = $order->getPayment()->getMethodInstance()->getCode();
            $session->clear();
        }

        if (in_array($paymentCode, $this->getPaymentMethodCc()) && $canceled == 0) {
            // charge credit card
            $charge = $this->creditcardCharge($order);

            // processing order
            //$this->updateOrder($order, $paymentCode, $charge);
            Mage::getModel('paymethod/vtdirect')->updateOrder($order, $paymentCode, $charge);
            Mage::register('response_charge', $charge);
            Mage::dispatchEvent('sales_order_place_after', array ('order' => $order));
        }
        
        /**
         * Charge Transaction (Mandiri E-Cash)
         */
        elseif (in_array($paymentCode, $this->getPaymentMethodVtdirect())) {
            $charge = $this->_vtdirectRedirectCharge($order);

            Mage::getModel('paymethod/vtdirect')->addHistoryOrder($order, $charge);
            Mage::register('response_charge', $charge);
            //Mage::dispatchEvent('sales_order_place_after', array ('order' => $order));
        }
        /**
         * Charge Virtual Account
         */
        elseif (in_array($paymentCode, $this->getPaymentMethodVA()) && $canceled == 0) {
            // charge credit card
            $charge = $this->_virtualAccountCharge($order);

            // processing order
            Mage::getModel('paymethod/vtdirect')->addHistoryOrder($order, $charge);
            Mage::register('response_charge', $charge);
        }

        // FDS (BILNA-1333) - Start
        if($canceled == 1) {
            $fraud = Mage::helper('core')->urlEncode('fraud');
            $this->_redirect('checkout/onepage/failure', array('fail' => $fraud));
            return;
        }
        // FDS (BILNA-1333) - End
        
        $this->loadLayout();
        $this->_initLayoutMessages('checkout/session');
        Mage::dispatchEvent('checkout_onepage_controller_success_action', array ('order_ids' => array ($lastOrderId), 'order' => $order));
        $this->renderLayout();
    }

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

    public function creditcardCharge($order) {
        Mage::helper('paymethod')->loadVeritransNamespace();

        // setting config vtdirect
        Veritrans_Config::$serverKey = $this->getVtdirectServerKey();
        Veritrans_Config::$isProduction = $this->getVtdirectIsProduction();

        $incrementId = $order->getIncrementId();
        $tokenId = $this->getTokenId();
        $grossAmount = $order->getGrandTotal();
        $paymentType = 'credit_card'; //hardcode

        // Optional
        //$billingAddress = $this->getBillingAddress();
        //$shippingAddress = $this->getShippingAddress();

        $paymentCode = $order->getPayment()->getMethodInstance()->getCode();
        $configBank = Mage::getStoreConfig('payment/' . $paymentCode);
        $acquiredBank = $this->_getAcquiredBank($configBank, $grossAmount);

        // Required
        $customerDetails = array (
            'first_name' => $order->getBillingAddress()->getFirstname(),
            'last_name' => $order->getBillingAddress()->getLastname(),
            'email' => $this->getCustomerEmail($order->getBillingAddress()->getEmail()),
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
                'status_message' => $e->getMessage()
            );
            $result = (object) $response;
        }

        return $result;
    }

    protected function _vtdirectRedirectCharge($order) {
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
    
    protected function _virtualAccountCharge($order) {
        Mage::helper('paymethod')->loadVeritransNamespace();

        //- setting config vtdirect
        Veritrans_Config::$serverKey = $this->getVtdirectServerKey();
        Veritrans_Config::$isProduction = $this->getVtdirectIsProduction();

        $incrementId = $order->getIncrementId();
        $grossAmount = $order->getGrandTotal();
        $payment = $order->getPayment();
        $paymentCode = $payment->getMethodInstance()->getCode();
        
        // Get Configuration Data
        $paymentConfig = Mage::getStoreConfig('payment/' . $paymentCode);
        $inquiryTextID = $paymentConfig['inquiry_text_indonesian'];
        $inquiryTextEN = $paymentConfig['inquiry_text_english'];
        $paymentTextID = $paymentConfig['payment_text_indonesian'];
        $paymentTextEN = $paymentConfig['payment_text_english'];
        $paymentType = $paymentConfig['vtdirect_payment_type'];
        $expiryDuration = $paymentConfig['expiry_duration'];
        $expiryDurationUnit = $paymentConfig['expiry_duration_unit'];
        $bank = $paymentConfig['bank'];

        $inquiryRowLimit = 5;
        $paymentRowLimit = 9;
        $maxTextLength = 38;

        // BEGIN - Replace freetext containing defined variables 
        $stringToReplaceArr = array(
            '{{order_no}}' => $incrementId
        );
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
        $inquiryTextArr = array();
        foreach($inquiryTextIDArr as $key=>$val){ // Loop though one array
            if (isset($inquiryTextENArr[$key])) {
                $val2 = $inquiryTextENArr[$key]; // Get the values from the other array
                $inquiryTextArr[$key] = $val + $val2; // combine 'em
            } else {
                $inquiryTextArr[$key] = $val;
            }
        }
        
        // Building Payment Text Data
        $paymentTextArr = array();
        foreach($paymentTextIDArr as $key=>$val){ // Loop though one array
            if (isset($paymentTextENArr[$key])) {
                $val2 = $paymentTextENArr[$key]; // Get the values from the other array
                $paymentTextArr[$key] = $val + $val2; // combine 'em
            } else {
                $paymentTextArr[$key] = $val;
            }
        }

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

        //- Data that will be sent for charge transaction request with Virtual Account.
        $transactionData = array (
            'payment_type' => $paymentType,
            'transaction_details' => $transactionDetails,
            'customer_details' => $customerDetails,
            $paymentType => array (
                'bank' => $bank,
                'free_text' => array(
                    'inquiry' => $inquiryTextArr,
                    'payment' => $paymentTextArr
                )
            ),
        );

        //If custom expiry is set, add custom expiry to the request
        if (!empty($expiryDuration)) {
            $transactionData['custom_expiry'] = array(
                "expiry_duration" => $expiryDuration,
                "unit" => $expiryDurationUnit
            );
        }

        try {
            $this->writeLog($paymentCode, $this->_typeTransaction, 'charge', 'request: ' . json_encode($transactionData));
            $result = Veritrans_VtDirect::charge($transactionData);
            $vaNumbers = $result->va_numbers[0];
            $payment->setVaNumber($vaNumbers->va_number);//Set value of va_number field from table sales_order_flat_payment
            $payment->save();
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
    
    protected function logProgress($message) {
        Mage::log($message, null, 'newstack.log');
    }

    public function getCheckout() {
        return Mage::getSingleton('checkout/session');
    }

    public function getOrderId() {
        return Mage::getSingleton('checkout/session')->getLastOrderId();
    }

    private function updateOrder($order, $paymentCode, $charge) {
        // check order status if processing/complete then ignore
        if (in_array($order->getStatus(), Mage::helper('paymethod/vtdirect')->getStatusOrderIgnore())) {
            return true;
        }

        $message = $charge->status_message;
        $transactionStatus = $charge->transaction_status;
        $fraudStatus = $charge->fraud_status;

        if ($transactionStatus == 'capture') {
            if ($fraudStatus == 'accept') {
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

                        return true;
                    }
                }
            }
            elseif ($fraudStatus == 'challenge') {
                $order->setState(Mage_Sales_Model_Order::STATE_NEW, 'cc_verification', $message, true);
                $order->save();

                return true;
            }
            elseif ($fraudStatus == 'deny') {
                $history = $order->addStatusHistoryComment($message);
                $history->setIsCustomerNotified(true);

                if ($order->canCancel()) {
                    $order->cancel();
                }

                $order->save();

                return true;
            }
            else {
                // do nothing
            }
        }
        elseif ($transactionStatus == 'challenge') {
            $order->setState(Mage_Sales_Model_Order::STATE_NEW, 'cc_verification', $message, true);
            $order->save();

            return true;
        }
        elseif ($transactionStatus == 'deny') {
            $history = $order->addStatusHistoryComment($message);
            $history->setIsCustomerNotified(true);

            if ($order->canCancel()) {
                $order->cancel();
            }

            $order->save();

            return true;
        }
        else {
            $order->addStatusHistoryComment('failed get response or timeout from Veritrans');
            $order->save();

            // write log to process confirmation
            $this->createLog($paymentCode, $this->maxChar($order->getIncrementId(), 20), 'confirmation', $order->getIncrementId() . "|Response charge is null");

            return true;
        }

        return false;
    }

    protected function getDefaultResponseMessage($status, $message) {
        $result = '';

        if ($message) {
            $result = $message;
        }
        else {
            if ($status == 'success') {
                $result = Mage::getStoreConfig('payment/vtdirect/default_response_message_success');
            }
            else if ($status == 'failure') {
                $result = Mage::getStoreConfig('payment/vtdirect/default_response_message_failure');
            }
            else {
                $result = Mage::getStoreConfig('payment/vtdirect/charge_timeout_message');
            }
        }

        return $result;
    }

    protected function getPaymentMethodCc() {
        return Mage::helper('paymethod')->getPaymentMethodCc();
    }

    protected function getPaymentMethodVtdirect() {
        return Mage::helper('paymethod')->getPaymentMethodVtdirect();
    }
    
    /**
     * Get List of Payment Method for Virtual Account
     */
    protected function getPaymentMethodVA()
    {
        return Mage::helper('paymethod')->getPaymentMethodVA();
    }
    
    protected function getTokenId() {
        $tokenId = Mage::getSingleton('core/session')->getVtdirectTokenId();

        /**
         * remove token_id session
         */
        Mage::getSingleton("core/session")->unsVtdirectTokenIdCreate();
        Mage::getSingleton("core/session")->unsVtdirectTokenId();

        return $tokenId;
    }

    protected function maxChar($text, $maxLength = 10) {
        if (empty ($text)) {
            return '';
        }

        return substr($text, 0, $maxLength);
    }

    protected function getBins($order, $paymentCode) {
        $digit = 6;
        //$digit = ($paymentCode == 'othervisa' || $paymentCode == 'othermc') ? 1 : 6;
        $result = substr($order->getPayment()->getCcBins(), 0, $digit);

        return array ($result);
    }

    protected function getOrderItems($order, $items) {
        $result = array ();

        //if (count($items) > 0) {
        //    foreach ($items as $itemId => $item) {
        //        $result[$itemId]['id'] = $this->maxChar($item->getProductId(), 20);
        //        $result[$itemId]['price'] = $item->getPrice();
        //        $result[$itemId]['qty'] = $item->getQtyToInvoice();
        //        $result[$itemId]['name'] = $this->maxChar($this->removeSymbols($item->getName()), 20);
        //    }
        //}
        $result[0]['id'] = $order->getId();
        $result[0]['price'] = $order->getGrandTotal();
        $result[0]['qty'] = 1;
        $result[0]['name'] = $this->maxChar('Item order ' . $order->getIncrementId(), 20);

        return $result;
    }

    protected function getCustomerEmail($email) {
        //if (Mage::getStoreConfig('payment/vtdirect/development_testing')) {
        //    return 'vt-testing@veritrans.co.id';
        //}

        return $email;
    }

    protected function parseShippingAddress($shippingAddress) {
        $firstname = $shippingAddress->getFirstname();
        $lastname = $shippingAddress->getFirstname();
        //$lastname = $shippingAddress->getLastname();

        $result = array (
            'first_name' => $this->maxChar(Mage::helper('paymethod/vtdirect')->filterAddress($firstname, true), 20),
            'last_name' => $this->maxChar(Mage::helper('paymethod/vtdirect')->filterAddress($lastname, true), 20),
            'address1' => $this->maxChar(Mage::helper('paymethod/vtdirect')->filterAddress($shippingAddress->getStreet(1)), 100),
            'address2' => $this->maxChar(Mage::helper('paymethod/vtdirect')->filterAddress($shippingAddress->getStreet(2)), 100),
            'city' => $this->maxChar(Mage::helper('paymethod/vtdirect')->filterAddress($shippingAddress->getCity()), 20),
            'postal_code' => $this->maxChar($this->getPostCode($shippingAddress->getPostcode()), 10),
            'phone' => $this->maxChar(Mage::helper('paymethod')->allowOnlyNumber($shippingAddress->getTelephone()), 19)
        );

        return $result;
    }

    protected function parseBillingAddress($billingAddress) {
        $firstname = $billingAddress->getFirstname();
        $lastname = $billingAddress->getFirstname();
        //$lastname = $billingAddress->getLastname();

        $result = array (
            'first_name' => $this->maxChar(Mage::helper('paymethod/vtdirect')->filterAddress($firstname, true), 20),
            'last_name' => $this->maxChar(Mage::helper('paymethod/vtdirect')->filterAddress($lastname, true), 20),
            'address1' => $this->maxChar(Mage::helper('paymethod/vtdirect')->filterAddress($billingAddress->getStreet(1)), 100),
            'address2' => $this->maxChar(Mage::helper('paymethod/vtdirect')->filterAddress($billingAddress->getStreet(2)), 100),
            'city' => $this->maxChar(Mage::helper('paymethod/vtdirect')->filterAddress($billingAddress->getCity()), 20),
            'postal_code' => $this->maxChar($this->getPostCode($billingAddress->getPostcode()), 10),
            'phone' => $this->maxChar(Mage::helper('paymethod')->allowOnlyNumber($billingAddress->getTelephone()), 19)
        );

        return $result;
    }

    protected function getPostCode($postCode) {
        $result = Mage::helper('paymethod')->allowOnlyNumber($postCode);

        if (empty ($result) || $result == '') {
            $result = Mage::helper('paymethod')->allowOnlyNumber($this->getPostCodeSession());
        }

        return $result;
    }

    protected function getPostCodeSession() {
        return Mage::getSingleton('core/session')->getVtdirectZipCode();
    }

    protected function getSecureBank($paymentCode) {
        return Mage::getStoreConfig('payment/' . $paymentCode . '/threedsecure');
    }

    protected function _getAcquiredBank($configBank, $grossAmount) {
        $result = $configBank['bank_acquired'];

        if ($configBank['threedsecure']) {
            if ($grossAmount >= $configBank['threedsecure_min_order_total']) {
                $result = $configBank['threedsecure_bank_acquired'];
            }
        }

        return $result;
    }

    protected function getInstallmentProcess($paymentCode) {
        return Mage::getStoreConfig('payment/' . $paymentCode . '/installment_process');
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

    protected function getInstallmentBank($paymentCode) {
        if (strtolower($paymentCode) == 'mandiripromovisa' || strtolower($paymentCode) == 'mandiripromomc') {
            return 'mandiri';
        }

        if (strtolower($paymentCode) == 'bnikartinivisa' || strtolower($paymentCode) == 'bnikartinimc') {
            return 'bni';
        }

        $result = '';

        if (substr($paymentCode, -4) == 'visa') {
            $result = substr($paymentCode, 0, -4);
        }
        else if (substr($paymentCode, -2) == 'mc') {
            $result = substr($paymentCode, 0, -2);
        }
        else {
            //do nothing
        }

        return $result;
    }

    protected function getInstallmentTypeCodeBank($paymentCode) {
        return Mage::getStoreConfig('payment/' . $paymentCode . '/installment_type_code');
    }

    protected function getThreedSecure($paymentCode) {
        $result = false;

        if (Mage::getStoreConfig('payment/' . $paymentCode . '/threedsecure')) {
            if (Mage::getStoreConfig('payment/' . $paymentCode . '/threedsecure') == 1) {
                $result = true;
            }
        }

        return $result;
    }

    protected function getThreedSecureCallbackUrl($paymentCode) {
        return Mage::getStoreConfig('payment/' . $paymentCode . '/threedsecure_callback_url');
    }

    protected function getThreedSecureNotificationUrl($paymentCode) {
        return Mage::getStoreConfig('payment/' . $paymentCode . '/threedsecure_notification_url');
    }

    protected function writeLog($paymentCode, $type, $logFile, $content) {
        $tdate = date('Ymd', Mage::getModel('core/date')->timestamp(time()));
        $filename = sprintf("%s_%s.%s", $paymentCode, $logFile, $tdate);
        $content = "[" . gethostname() . "] " . $content;

        return Mage::helper('paymethod')->writeLogFile($paymentCode, $type, $filename, $content);
    }

    protected function createLock($paymentCode, $filename) {
        return Mage::helper('paymethod')->createLockFile($paymentCode, $filename);
    }

    protected function checkLock($paymentCode, $filename) {
        return Mage::helper('paymethod')->checkLockFile($paymentCode, $filename);
    }

    protected function createLog($paymentCode, $filename, $type, $content) {
        $tdate = date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(time()));
        $content = sprintf("%s|%s", $content, $tdate);

        return Mage::helper('paymethod')->writeLogFile($paymentCode, $type, $filename, $content, 'normal');
    }

    /**
     * Function to parse text for Veritrans transaction
     * @param $textToParse Array of text
     * @param $languageKey string Language Key that will be used in the index
     * @param $rowLimit int Limit of the return rows
     * @param $maxRowLength int Maximum length of each row returned
     * @return array of parsed text
     */
    protected function _parseFreeText($textToParse, $languageKey='id', $rowLimit = 5, $maxRowLength = 38)
    {
        $parsedText = array();
        $splittedText = explode(PHP_EOL, $textToParse); 
        $index = 0;

        $numRow = count($splittedText);
        if ($numRow > $rowLimit) {
            $numRow = $rowLimit;
        }

        if (!empty($splittedText)) {
            for ($i = 0; $i < $numRow; $i++) {
                if (!empty($splittedText[$i])) {
                    $parsedText[$index][$languageKey] = substr(trim($splittedText[$i]), 0, $maxRowLength);
                    $index++;
                }
            }
        }
        return $parsedText;
    }

    /**
     * Function to validate Order for Cross Border
     */
    public function validateOrderAction()
    {
        if ($this->_expireAjax()) {
            return;
        }

        if ($this->getRequest()->isPost()) {
            $result = array();
            $result['success'] = true;
            $result['error_messages'] = '';
            $crossBorderModel = Mage::getModel('bilna_crossborder/CrossBorder');

            $validationResult = $crossBorderModel->validate();
            if ($validationResult['success'] == false) {
                $result['success'] = false;
                $result['error_messages'] = $validationResult['message'];
            }

            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }
}