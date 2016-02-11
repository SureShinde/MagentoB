<?php

/**
 * API2 class for affiliate (admin)
 *
 * @category   AW
 * @package    AW_Affiliate
 * @author     Development Team <development@bilna.com>
 */
class AW_Affiliate_Model_Api2_Requestwithdrawal_Rest_Admin_V1 extends AW_Affiliate_Model_Api2_Requestwithdrawal_Rest
{
    
    protected $_customerId = null;
    
    protected function _retrieve() 
    {   
        $customerId = $this->getRequest()->getParam('customer_id');
        $amount = $this->getRequest()->getParam('amount');
        $details = $this->getRequest()->getParam('details');
        
        $params = array(
            'customer_id' => $customerId, 
            'amount' => $amount, 
            'details' => $details
        );
        //var_dump($params);die;
        $this->_customerId = $params['customer_id'];
        
        return $this->withdrawalRequestCreate($params);
    }

    protected function withdrawalRequestCreate($postData)
    {
        if (!$this->_customerId) {
            return 'Customer is not logged in';
        }
        
        $messages = array();
        $isError = false;
        $response = new Varien_Object();
        $response->setError(0);
        
        $affiliate = Mage::getModel('awaffiliate/affiliate');
        if ($this->_customerId) {
            $affiliate->loadByCustomerId($this->_customerId);
        }

        $affiliateId = intval($affiliate->getId());
        if ($affiliateId < 1) {
            $response->setError(1);
            $isError = true;
            $messages[] = Mage::helper('awaffiliate')->__('Unable to get the affiliate ID');
        }
        $amount = intval($postData['amount']);
        if (is_null($amount) || ($amount < 1)) {
            $response->setError(1);
            $isError = true;
            $messages[] = Mage::helper('awaffiliate')->__('Incorrect amount');
        }

        if (!$isError && !Mage::helper('awaffiliate/affiliate')->isWithdrawalRequestAvailableOn($affiliate, $amount)) {
            
            $response->setError(1);
            $messages[] = Mage::helper('awaffiliate')->__('This amount is not available for request');
        }
        if (Mage::helper('awaffiliate/config')->getMinimumAmountToWithdraw(Mage::app()->getStore(true)->getId()) > $amount) {
            $response->setError(1);
            $isError = true;
            $messages[] = Mage::helper('awaffiliate')->__('Requested amount is insufficient to withdraw. Minimal request amount is %d %s',
                Mage::helper('awaffiliate/config')->getMinimumAmountToWithdraw(Mage::app()->getStore(true)->getId()), Mage::app()->getBaseCurrencyCode());
        }
        if (!$isError) {
            $withdrawalRequest = Mage::getModel('awaffiliate/withdrawal_request');
            $withdrawalRequest->setAmount($amount);
            $withdrawalRequest->setDescription(strip_tags($postData['details']));
            $withdrawalRequest->setAffiliateId($affiliateId);
            $withdrawalRequest->setData('created_at', Mage::getModel('core/date')->gmtDate());
            try {
                $withdrawalRequest->save();
                $messages[] = Mage::helper('awaffiliate')->__('Withdrawal request saved');
            } catch (Exception $e) {
                $messages[] = $e->getMessage();
            }
        }
        
        return array(
            'message' => $messages, 
            'response' => $response, 
            'data' => $postData 
        );
    }
}