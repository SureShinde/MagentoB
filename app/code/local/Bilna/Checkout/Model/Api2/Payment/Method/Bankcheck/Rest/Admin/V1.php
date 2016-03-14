<?php
/**
 * Description of Bilna_Checkout_Model_Api2_Payment_Method_Bankcheck_Rest_Admin_V1
 * 
 * @pakage Bilna_Checkout
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Checkout_Model_Api2_Payment_Method_Bankcheck_Rest_Admin_V1 extends Bilna_Checkout_Model_Api2_Payment_Method_Bankcheck_Rest {
    protected function _retrieve() {
        $bin = $this->getRequest()->getParam('bin');
        
        if (!in_array($bin[0], array (4,5))) {
            $this->_critical('Please enter a valid credit card number.');
        }
        
        $bankCode = Mage::getModel('paymethod/method_vtdirect')->getBankCode($bin);
        $ccType = $this->getCcType($bankCode);
        $configBank = Mage::getStoreConfig('payment/' . $bankCode);
        $acquiredBank = $configBank['bank_acquired'];
        $secure = $configBank['threedsecure'];
        $secureAcquiredBank = isset ($configBank['threedsecure_bank_acquired']) ? $configBank['threedsecure_bank_acquired'] : '0';
        $secureMinimum = (int) $configBank['threedsecure_min_order_total'];
        $installmentProcess = $configBank['installment_process'];
        
        $response = array (
            'bank_code' => $bankCode,
            'cc_type' => $ccType,
            'acquired_bank' => $acquiredBank,
            'secure' => $secure,
            'secure_acquired_bank' => $secure ? $secureAcquiredBank : $acquiredBank,
            'secure_min' => $secure ? $secureMinimum : 0,
            'installment_process' => $installmentProcess,
        );
        
        return $response;
    }

    protected function getCcType($bank) {
        $ccType = (strtoupper(substr($bank, -2)) == 'MC') ? 'MC' : 'VI';
        
        return $ccType;
    }

    protected function getAcquiredBank($paymentCode) {
        return Mage::getStoreConfig('payment/' . $paymentCode . '/bank_acquired');
    }
    
    protected function getSecureBank($paymentCode) {
        return Mage::getStoreConfig('payment/' . $paymentCode . '/threedsecure');
    }

    protected function getInstallmentProcess($paymentCode) {
        return Mage::getStoreConfig('payment/' . $paymentCode . '/installment_process');
    }
}
