<?php
/**
 * Rocket Web Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is available through the world-wide-web at this URL:
 * http://www.rocketweb.com/RW-LICENSE.txt
 *
 * @category   RocketWeb
 * @package    RocketWeb_Netsuite
 * @copyright  Copyright (c) 2013 RocketWeb (http://www.rocketweb.com)
 * @author     Rocket Web Inc.
 * @license    http://www.rocketweb.com/RW-LICENSE.txt
 */
class RocketWeb_Netsuite_Model_Process_Export_Order_Place extends RocketWeb_Netsuite_Model_Process_Export_Abstract {

    /**
     * Submits the order to Netsuite
     *
     * @param RocketWeb_Netsuite_Model_Queue_Message $message
     * @throws Exception
     */
    public function process(RocketWeb_Netsuite_Model_Queue_Message $message, $queueData = array ()) {

        if(!$message || !$message->getEntityId()) {
            throw new Exception("Message not initialized");
        }

        $magentoOrder = Mage::getModel('sales/order')->load($message->getEntityId());
        if(!$magentoOrder || !$magentoOrder->getId()) {
            throw new Exception("Cannot load order with id #{$message->getEntityId()} from Magento!");
        }

        $is_oneworld = Mage::helper('rocketweb_netsuite')->checkOneWorld($magentoOrder->getCreatedAt());

        if ($is_oneworld)
            $this->processOneWorld($message, $queueData, $magentoOrder);
        else
            $this->processOld($message, $queueData, $magentoOrder);
    }

    protected function processOneWorld(RocketWeb_Netsuite_Model_Queue_Message $message, $queueData = array (), $magentoOrder) {

        $netsuiteService = Mage::helper('rocketweb_netsuite')->getNetsuiteService();

        /* addition by Willy
        - if the processing field in message table is 1,
        check whether the order from Magento already exists in Netsuite
        - if exists but the magento does not have netsuite internal ID, we have to save the internal id
        - if exists and the magento already has netsuite internal ID, no need to proceed with anything
        - if not exists, we have to transfer data to Netsuite and then, save the internal id
        */
        if ($queueData['processing'] == 1)
        {
            $order_id = $magentoOrder->getIncrementId();
            $netsuite_internal_id = $magentoOrder->getNetsuiteInternalId();
            $orderExists = Mage::helper('rocketweb_netsuite/mapper_order')->findNetsuiteRequestOrder($order_id);
            if ($orderExists && (is_null($netsuite_internal_id) || trim($netsuite_internal_id) == ''))
            {
                // save internal ID
                $magentoOrder->setNetsuiteInternalId($orderExists);
                $magentoOrder->getResource()->save($magentoOrder);
                return;
            }
            else
            if ($orderExists && !(is_null($netsuite_internal_id) || trim($netsuite_internal_id) == ''))
                return;
        }
        /* end of addition by Willy */

        // for header purposes
        $export_config_same = (int) Mage::getStoreConfig('rocketweb_netsuite/connection_export/same');
        $nlauth_account = Mage::getStoreConfig('rocketweb_netsuite/general/account_id');
        $ns_host = Mage::getStoreConfig('rocketweb_netsuite/general/host');

        if ( $export_config_same == 1 )
        {
            $nlauth_email = Mage::getStoreConfig('rocketweb_netsuite/general/email');
            $nlauth_signature = Mage::getStoreConfig('rocketweb_netsuite/general/password');
            $nlauth_role = Mage::getStoreConfig('rocketweb_netsuite/general/role_id');
        }
        else
        {
            $nlauth_email = Mage::getStoreConfig('rocketweb_netsuite/connection_export/email');
            $nlauth_signature = Mage::getStoreConfig('rocketweb_netsuite/connection_export/password');
            $nlauth_role = Mage::getStoreConfig('rocketweb_netsuite/connection_export/role_id');
        }

        // check whether the url is sandbox or not
        if (strpos($ns_host, 'sandbox') !== false)
        {
            $ns_rest_host = "https://rest.sandbox.netsuite.com";
            $scriptId = Mage::getStoreConfig('rocketweb_netsuite/requestorder/sandbox_create_rst_id');
        }
        else
        {
            $ns_rest_host = "https://rest.netsuite.com";
            $scriptId = Mage::getStoreConfig('rocketweb_netsuite/requestorder/production_create_rst_id');
        }

        if (!$scriptId)
            return;

        // variables to be posted to create request order
        $vars = Mage::helper('rocketweb_netsuite/mapper_order')->createPostParams($magentoOrder);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, ($ns_rest_host . "/app/site/hosting/restlet.nl?script=$scriptId&deploy=1"));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($vars));  //Post Fields
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $headers = array();
        $headers[] = 'Authorization: NLAuth nlauth_account='.$nlauth_account.',nlauth_email='.$nlauth_email.',nlauth_signature='.$nlauth_signature.',nlauth_role='.$nlauth_role;
        //$headers[] = 'Accept-Encoding: gzip, deflate';
        //$headers[] = 'Accept-Language: en-US,en;q=0.5';
        //$headers[] = 'Cache-Control: no-cache';
        $headers[] = 'Content-Type: application/json;';
        $headers[] = 'User-Agent-x: SuiteScript-Call';

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $server_output = curl_exec($ch);

        curl_close($ch);

        $server_output = json_decode($server_output, true);

        $request_order_create_new_log_file = 'request_order_create_new.log';

        if ($server_output['status'] == 'success')
        {
            $netsuite_internal_id = $server_output['internalid'];
            $magentoOrder->setNetsuiteInternalId($netsuite_internal_id);
            $magentoOrder->getResource()->save($magentoOrder);

            Mage::log(('SO #' . $magentoOrder->getIncrementId() . ' create RO with ID ' . $netsuite_internal_id), null, $request_order_create_new_log_file);
        }
        else
        if ($server_output['status'] == 'error')
        {
            Mage::log(('SO #' . $magentoOrder->getIncrementId() . ' failed with status ' . $server_output['msg']), null, $request_order_create_new_log_file);
           throw new Exception("Failed to create request order. Status : " . $server_output['msg']);        
        }
    }

    /**
     * Submits the order to Netsuite
     *
     * @param RocketWeb_Netsuite_Model_Queue_Message $message
     * @throws Exception
     */
    protected function processOld(RocketWeb_Netsuite_Model_Queue_Message $message, $queueData = array (), $magentoOrder) {

        $netsuiteService = Mage::helper('rocketweb_netsuite')->getNetsuiteService();

        /* addition by Willy
        - if the processing field in message table is 1,
        check whether the order from Magento already exists in Netsuite
        - if exists but the magento does not have netsuite internal ID, we have to save the internal id
        - if exists and the magento already has netsuite internal ID, no need to proceed with anything
        - if not exists, we have to transfer data to Netsuite and then, save the internal id
        */
        if ($queueData['processing'] == 1)
        {
            $order_id = $magentoOrder->getIncrementId();
            $netsuite_internal_id = $magentoOrder->getNetsuiteInternalId();
            $orderExists = Mage::helper('rocketweb_netsuite/mapper_order')->findNetsuiteSalesOrder('tranId', $order_id);
            if ($orderExists && (is_null($netsuite_internal_id) || trim($netsuite_internal_id) == ''))
            {
                // save internal ID
                $magentoOrder->setNetsuiteInternalId($orderExists);
                $magentoOrder->getResource()->save($magentoOrder);
                return;
            }
            else
            if ($orderExists && !(is_null($netsuite_internal_id) || trim($netsuite_internal_id) == ''))
                return;
        }
        /* end of addition by Willy */

        $netsuiteOrder = Mage::helper('rocketweb_netsuite/mapper_order')->getNetsuiteFormat($magentoOrder);

        Mage::dispatchEvent('netsuite_new_order_send_before',array('magento_order'=>$magentoOrder,'netsuite_order'=>$netsuiteOrder));

        $request = new AddRequest();
        $request->record = $netsuiteOrder;
        $response = $netsuiteService->add($request);

        if($response->writeResponse->status->isSuccess) {
            $netsuiteId = $response->writeResponse->baseRef->internalId;
            $magentoOrder->setNetsuiteInternalId($netsuiteId);
            $magentoOrder->getResource()->save($magentoOrder);

        }
        else {
            throw new Exception((string) print_r($response->writeResponse->status->statusDetail,true));
        }
    }

}