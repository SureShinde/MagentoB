<?php

/**
 * API2 class for payment method bacnk check (admin)
 *
 * @category   Bilna
 * @package    Bilna_Checkout
 * @author     Development Team <development@bilna.com>
 */
class Bilna_Checkout_Model_Api2_Payment_Method_Bankcheck_Rest_Admin_V1 extends Bilna_Checkout_Model_Api2_Quote_Rest
{
	/**
     * Bank check payment method
     *
     * @param int|string $store
     * @return int
     */
    /*protected function _create(array $data)
    {
    	$cardNo = $data['card_no'];
    	$response = array (
            'status' => false,
            'data' => array (),
            'message' => null
        );

        try {
        	if (in_array ($cardNo[0], array (4,5)))
        	{
        		$bankCode = Mage::getModel('paymethod/method_vtdirect')->getBankCode($cardNo);
            	$ccType = $this->getCcType($bankCode);
				
				$response['status'] = true;
	            $response['data'] = array (
	                'bank_code' => $bankCode,
	                'cc_type' => $ccType,
	                'acquired_bank' => $this->getAcquiredBank($bankCode),
	                'secure' => $this->getSecureBank($bankCode),
	                'installment_process' => $this->getInstallmentProcess($bankCode),
                    'client_key' => Mage::getStoreConfig('payment/vtdirect/client_key')
	            );
	        }
	        else {
	            $response['message'] = 'Please enter a valid credit card number.';
	        }

        } catch (Mage_Core_Exception $e) {
            $this->_error($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        }

        return $response;
    }*/

    /**
     * Get Bank Check Validation
     *
     * @param  $quoteId
     * @param  $shippingMethod
     * @param  $store
     * @return bool
     */
    protected function _retrieve()
    {
        $cardNo = $this->getRequest()->getParam('id');
        $response = array (
            'status' => false,
            'data' => array (),
            'message' => null
        );

        try {
            if (in_array ($cardNo[0], array (4,5)))
            {
                $bankCode = Mage::getModel('paymethod/method_vtdirect')->getBankCode($cardNo);
                $ccType = $this->getCcType($bankCode);
                
                $response['status'] = true;
                $response['data'] = array (
                    'bank_code' => $bankCode,
                    'cc_type' => $ccType,
                    'acquired_bank' => $this->getAcquiredBank($bankCode),
                    'secure' => $this->getSecureBank($bankCode),
                    'installment_process' => $this->getInstallmentProcess($bankCode),
                    'client_key' => Mage::getStoreConfig('payment/vtdirect/client_key')
                );
            }
            else {
                $response['message'] = 'Please enter a valid credit card number.';
            }

        } catch (Mage_Core_Exception $e) {
            $this->_error($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        }

        return $response;
    }

    protected function getCcType($bank)
    {
        $ccType = (strtoupper(substr($bank, -2)) == 'MC') ? 'MC' : 'VI';
        
        return $ccType;
    }

    protected function getAcquiredBank($paymentCode)
    {
        return Mage::getStoreConfig('payment/' . $paymentCode . '/bank_acquired');
    }
    
    protected function getSecureBank($paymentCode)
    {
        return Mage::getStoreConfig('payment/' . $paymentCode . '/threedsecure');
    }

    protected function getInstallmentProcess($paymentCode)
    {
        return Mage::getStoreConfig('payment/' . $paymentCode . '/installment_process');
    }
}