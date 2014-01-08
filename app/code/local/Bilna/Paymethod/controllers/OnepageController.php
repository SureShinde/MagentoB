<?php
/**
 * Description of Bilna_Paymethod_OnepageController
 *
 * @author Bilna Development Team <development@bilna.com>
 */

require_once 'Mage/Checkout/controllers/OnepageController.php';

class Bilna_Paymethod_OnepageController extends Mage_Checkout_OnepageController {
    protected $_payType = '';
    
    public function saveOrderAction() {
        $paymentAllow = array ('klikpay', 'anzcc', 'scbcc');
        $paymentCode = Mage::getSingleton('checkout/session')->getQuote()->getPayment()->getMethodInstance()->getCode();
        
        if (in_array($paymentCode, $paymentAllow)) {
            if ($this->_expireAjax()) {
                return;
            }

            $result = array ();
               
            try {
                //save installment options in quote item table 
                if ($installmentData = $this->getRequest()->getPost('installment', false)) {
                    $quote_items = $this->getOnepage()->getQuote()->getAllItems();
                    $item_ids = array ();
                       
                    foreach ($quote_items as $item) {
                        $item_ids[] = $item->getProductId();  
                    }
                    
                    $installmentOptionType = Mage::getStoreConfig('payment/' . $paymentCode . '/installment_option');
                       
                    if ($installmentOptionType == 2) { // if installment type is per order
                        if ($installmentData == '') {
                            $result['success'] = false;
                            $result['error'] = true;
                            $result['error_messages'] = $this->__('Please select an installment type before placing the order.');
                            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                            
                            return;
                        }
                        
                        foreach ($quote_items as $item) {
                            $item->setInstallmentType($installmentData);
                            $item->save();
                        }
                        
                        if ($installmentData == $this->getPaymentTypeTransaction($paymentCode, 'full')) {
                            $this->_payType = $this->getPaymentTypeTransaction($paymentCode, 'full');
                        }
                        else {
                            $this->_payType = $this->getPaymentTypeTransaction($paymentCode, 'installment');
                        }
                    }
                    else { //if installment type is per item
                        if (array_diff($item_ids, array_keys($installmentData))) {
                            $result['success'] = false;
                            $result['error'] = true;
                            $result['error_messages'] = $this->__('Please select an installment type before placing the order.');
                            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                            
                            return;
                        }
                        
                        foreach ($quote_items as $item) {
                            foreach ($installmentData as $_productid => $installmentvalue) {
                                if ($item->getProductId() == $_productid) {
                                    $item->setInstallmentType($installmentvalue);
                                    $item->save();
                                }
                            }
                        }
                           
                        //save pay type 
                        $count = 0;
                        $arrayCount  = count($installmentData);
                        
                        foreach ($installmentData as $value) {
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
                }
                else {
                    $result['success'] = false;
                    $result['error'] = true;
                    $result['error_messages'] = $this->__('Please select an installment type before placing the order.');
                    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                    
                    return;
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
                $result['error']   = false;
            }
            catch (Mage_Payment_Model_Info_Exception $e) {
                $message = $e->getMessage();
                
                if (!empty ($message)) {
                    $result['error_messages'] = $message;
                }
                
                $result['goto_section'] = 'payment';
                $result['update_section'] = array(
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
            
            /**
             * save parameter token_id to session
             */
            if ($data['method'] == 'vtdirect') {
                Mage::getSingleton('core/session')->setVtdirectTokenIdCreate(date('yyyy-mm-dd H:i:s'));
                Mage::getSingleton('core/session')->setVtdirectTokenId($data['token_id']);
            }
            
            $result = $this->getOnepage()->savePayment($data);

            // get section and redirect data
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
            
            $response['status'] = true;
            $response['data'] = array (
                'bank_code' => $bankCode,
                'cc_type' => $ccType
            );
        }
        else {
            $response['message'] = 'Please enter a valid credit card number.';
        }
        
        echo json_encode($response);
        exit;
    }
    
    private function getCcType($bank) {
        $bankArr = explode('_', $bank);
        $ccType = (strtoupper($bankArr[1]) == 'VISA') ? 'VI' : 'MC';
        
        return $ccType;
    }
}
