<?php

/**
 * API2 class for paymethod (admin)
 *
 * @category   Bilna
 * @package    Bilna_Paymethod
 * @author     Development Team <development@bilna.com>
 */
class Bilna_Paymethod_Model_Api2_Installment_Rest_Admin_V1 extends Bilna_Paymethod_Model_Api2_Installment_Rest
{
	protected function _retrieve()
    {
    	$quoteId = $this->getRequest()->getParam('id');
    	$storeId = 1;
    	$data = array();

    	try{
	    	$quote = $this->_getQuote($quoteId, $storeId);
	    	$totals = $quote->getTotals();

	    	$bankCode = $quote->getPayment()->getMethodInstance()->getCode();

	    	$isSupportInstallment = Mage::getStoreConfig('payment/' . $bankCode . '/allow_installment');

	    	if($isSupportInstallment)
	    	{
		    	$installmentOption = unserialize(Mage::getStoreConfig('payment/' . $bankCode . '/installment'));

		    	if(!empty($installmentOption))
		    	{
		    		foreach ($installmentOption as $option)
		    		{
		    			if (!empty ($option['tenor']) || $option['tenor'] != ''){
		    				if (!$this->getInstallmentFeature($totals,$bankCode))
		    					continue;
		    			}
		    			$data[] = $option;
		    		}
		    	}
	    	}

    	} catch (Mage_Core_Exception $e) {
            $this->_error($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        }

        return array('data' => $data);

    }

    public function getInstallmentFeature($totals, $bankCode) 
    {
        $subTotal = round($totals['subtotal']->getValue()); //Subtotal value
        $grandTotal = round($totals['grand_total']->getValue()); //Grandtotal value
        $minOrderTotalInstallment = Mage::getStoreConfig('payment/' . $bankCode . '/min_installment_total');
        $maxOrderTotalInstallment = Mage::getStoreConfig('payment/' . $bankCode . '/max_installment_total');
        $minOrderTotalCheck = false;
        $maxOrderTotalCheck = false;
        
        // check minimum order total
        if (empty ($minOrderTotalInstallment) || $minOrderTotalInstallment == '') {
            $minOrderTotalCheck = true;
        }
        else {
            if ($grandTotal >= $minOrderTotalInstallment) {
                $minOrderTotalCheck = true;
            }
        }
        
        // check maximum order total
        if (empty ($maxOrderTotalInstallment) || $maxOrderTotalInstallment == '') {
            $maxOrderTotalCheck = true;
        }
        else {
            if ($grandTotal <= $maxOrderTotalInstallment) {
                $maxOrderTotalCheck = true;
            }
        }
        
        if ($minOrderTotalCheck && $maxOrderTotalCheck) {
            return true;
        }
        
        return false;
    }

}