<?php

/**
 * API2 class for affiliate (admin)
 *
 * @category   AW
 * @package    AW_Affiliate
 * @author     Development Team <development@bilna.com>
 */
class AW_Affiliate_Model_Api2_Ordercomplete_Rest_Admin_V1 extends AW_Affiliate_Model_Api2_Ordercomplete_Rest
{   
    protected function _retrieve()
    {
    	$client = $this->getRequest()->getParam('client_id');
        $order = $this->getRequest()->getParam('order_id');
        $messages = array();
        $data = array();
        $status = 'success';
        if (!is_null($client)) {
            $clientModel = Mage::getModel('awaffiliate/client')->load($client);
            $orderModel = Mage::getModel('sales/order')->load($order); //order id = entity if from sales_flat_order table
            $affiliateModel = Mage::getModel('awaffiliate/affiliate')->load($clientModel->getAffiliateId());
            if (is_null($clientModel->getId())) {
                $status = 'error';
                $messages[] = Mage::helper('awaffiliate')->__('Unable to get the client ID');
            }
            if ($orderModel->getCustomerId() == $affiliateModel->getCustomerId()) {
                $status = 'error';
                $messages[] = Mage::helper('awaffiliate')->__('Cannot process affiliate with similiar customer ID');
            }
            $historyModel = Mage::getModel('awaffiliate/client_history');
            $data = array(
                'client_id' => $clientModel->getId(),
                'action' => AW_Affiliate_Model_Source_Client_Actions::ORDER_PLACED,
                'linked_item_id' => $orderModel->getId(),
                'created_at' => Mage::getModel('core/date')->gmtDate(),
                'params' => array()
            );
            $historyModel->setData($data);
            
            try {
                $historyModel->save();
                $status = 'success';
                $messages[] = Mage::helper('awaffiliate')->__('Affiliate client history saved successfully');
            } catch (Exception $e) {
                $status = 'error';
                $messages[] = Mage::helper('awaffiliate')->__($e->getMessage());
            }
        }
        
        return array(
            'message' => $messages, 
            'status' => $status, 
            'data' => $data 
        );
    }
}