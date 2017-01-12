<?php

/**
 * affiliate api resource
 *
 * @category   AW
 * @package    AW_Affiliate
 * @author     Bilna Development Team <core@magentocommerce.com>
 */
class AW_Affiliate_Model_Api2_Ordercomplete extends Mage_Api2_Model_Resource
{    
    const DEFAULT_STORE_ID = 1;

    public function __construct() 
    {
        Mage::app()->getStore()->setStoreId(self::DEFAULT_STORE_ID);
    }

    public function saveClientHistory($params = array())
    {
        $client = $params['client_id'];
        $order = $params['order_id'];
        $messages = array();
        $data = array();
        $status = 'success';
        if (!is_null($client) && !is_null($order)) {
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
                $this->createTransaction(array(
                    'client_id' => $client,
                    'order_id' => $order
                ));
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

    public function createTransaction($params = array())
    {
        $clientModel = Mage::getModel('awaffiliate/client')->load($params['client_id']);
        if (is_null($clientModel->getId())) {
            return FALSE;
        }
        $getInvoice = FALSE;
        $historyCollection = Mage::getModel('awaffiliate/client_history')->load($clientModel->getId());
        $client = $historyCollection->getClient();
        $orderModel = Mage::getModel('sales/order')->load($params['order_id']);
        $campaign = $client->getCampaign();
        $conditionsModel = $campaign->getConditionsModel();

        if ($orderModel->hasInvoices()) {
            foreach ($orderModel->getInvoiceCollection() as $invoice) {
                $getInvoice = $invoice;
            }
        }
        if ($conditionsModel->getActions()->validate($orderModel)) {
            /** @var $trx AW_Affiliate_Model_Transaction_Profit */
            $dataToSet = array(
                'campaign_id' => $clientModel->getCampaignId(),
                'affiliate_id' => $clientModel->getAffiliateId(),
                'traffic_id' => NULL,
                'store_id' => self::DEFAULT_STORE_ID,
                'client_id' => $clientModel->getId(),
                'linked_entity_type' => AW_Affiliate_Model_Source_Transaction_Profit_Linked::INVOICE_ITEM,
                'linked_entity_id' => $orderModel->getIncrementId(),
                'linked_entity_invoice' => $getInvoice,
                'linked_entity_order' => $orderModel,
                'created_at' => Mage::getModel('core/date')->gmtDate(),
                'type' => AW_Affiliate_Model_Source_Transaction_Profit_Type::CUSTOMER_PURCHASE
            );

            $trx = Mage::getModel('awaffiliate/transaction_profit', $dataToSet);
            $trx->setData($dataToSet);
            $trx->createTransaction();
        }
    }

    public function findAffiliateClientId($orderId = NULL)
    {
        $orderModel = Mage::getModel('sales/order')->load($orderId);
        if ($orderModel->getId()) {
            $clientModel = Mage::getModel('awaffiliate/client_history')->load($orderId, 'linked_item_id');
            
            return $clientModel->getClientId();
        }

        return FALSE;
    }
}