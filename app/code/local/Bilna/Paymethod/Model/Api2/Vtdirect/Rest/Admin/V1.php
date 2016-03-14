<?php
/**
 * API2 class for paymethod (admin)
 *
 * @category   Bilna
 * @package    Bilna_Paymethod
 * @author     Development Team <development@bilna.com>
 */

class Bilna_Paymethod_Model_Api2_Vtdirect_Rest_Admin_V1 extends Bilna_Paymethod_Model_Api2_Vtdirect_Rest {
    
    protected function _retrieve() {
    	$incrementId = $this->getRequest()->getParam('id');

    	try {
            //temporary disabled
            /*$responseCharge = Mage::getModel('paymethod/veritrans')
                ->setData('increment_id', $incrementId)
                ->selectData(array (
                    'order_id',
                    'increment_id',
                    'gross_amount',
                    'payment_type',
                    'bank',
                    'token_id',
                    'status_code',
                    'status_message',
                    'transaction_id',
                    'transaction_time',
                    'transaction_status',
                    'fraud_status',
                    'approval_code'
	    	))->fetchAll();
             * 
             */
            
            //use new table response
            $responseCharge = $this->getQuery($incrementId);
    	}
        catch (Mage_Core_Exception $e) {
            $this->_critical($e->getMessage());
        }

        return json_decode($responseCharge['response'], true);
    }

}