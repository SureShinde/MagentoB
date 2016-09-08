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

class RocketWeb_Netsuite_Model_Process_Export_Invoice_Save extends RocketWeb_Netsuite_Model_Process_Export_Abstract {
    /**
     * Submits the invoice to Netsuite
     *
     * @param RocketWeb_Netsuite_Model_Queue_Message $message
     * @throws Exception
     */
    public function process(RocketWeb_Netsuite_Model_Queue_Message $message, $queueData = array ()) {
        if (!$message || !$message->getEntityId()) {
            throw new Exception("Message not initialized");
        }

        $magentoInvoice = Mage::getModel('sales/order_invoice')->load($message->getEntityId());
        
        if (!$magentoInvoice || !$magentoInvoice->getId()) {
            throw new Exception("Cannot load invoice with id #{$message->getEntityId()} from Magento!");
        }
        
        // save $message to log file
        if (is_array($queueData) && count($queueData) > 0) {
            $this->createMagentoInvoiceLog($queueData);
        }
        
        // check invoice already exist
        if ($magentoInvoice->getNetsuiteInternalId()) {
            //throw new Exception("Invoice #{$message->getEntityId()} exit process manually!");
            return;
        }

        $magentoOrder = $magentoInvoice->getOrder();

        $is_oneworld = Mage::helper('rocketweb_netsuite')->checkOneWorld($magentoOrder->getCreatedAt());

        if ($is_oneworld)
            $this->processOneWorld($message, $queueData, $magentoOrder, $magentoInvoice);
        else
            $this->processOld($message, $queueData, $magentoOrder, $magentoInvoice);
    }

    protected function processOneWorld(RocketWeb_Netsuite_Model_Queue_Message $message, $queueData = array (), $magentoOrder, $magentoInvoice) {

        $netsuiteService = Mage::helper('rocketweb_netsuite')->getNetsuiteService();

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
            $scriptId = Mage::getStoreConfig('rocketweb_netsuite/proformainvoice/sandbox_export_rst_id');
        }
        else
        {
            $ns_rest_host = "https://rest.netsuite.com";
            $scriptId = Mage::getStoreConfig('rocketweb_netsuite/proformainvoice/production_export_rst_id');
        }

        if (!$scriptId)
            throw new Exception("Please input Restlet ID for creating Proforma Invoice");

        $orderInternalId = $magentoInvoice->getOrder()->getNetsuiteInternalId();

        // do not continue if there is no internal ID
        if (!$orderInternalId)
            throw new Exception("There is no order related to the magento invoice with entity ID : " . $magentoInvoice->getId());

        $invoiceDate = $magentoInvoice->getCreatedAt();
        $invoiceDate = Mage::getModel('core/date')->date('j/n/Y', strtotime($invoiceDate));

        // variables to be posted to proforma
        $vars = array(
            'magento_invoice_id' => $magentoInvoice->getIncrementId(),
            'netsuite_order_internalid' => $orderInternalId,
            'magento_invoice_date' => $invoiceDate
        );

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

        $proforma_create_new_log_file = 'proforma_create_new.log';

        if ($server_output !== false)
        {
            $server_output = json_decode($server_output, true);
            if ($server_output['status'] == 'success')
            {
                // if successful, save the internal ID into magento's invoice
                $magentoInvoice->setNetsuiteInternalId($orderInternalId);
                $magentoInvoice->getResource()->save($magentoInvoice);

                Mage::log(('Invoice #' . $magentoInvoice->getIncrementId() . ' create Proforma with ID ' . $server_output['internalid']), null, $proforma_create_new_log_file);
            }
            else
            if ($server_output['status'] == 'error')
            {
                Mage::log(('Invoice #' . $magentoInvoice->getIncrementId() . ' failed with status ' . $server_output['msg']), null, $proforma_create_new_log_file);
                throw new Exception("Failed to create proforma invoice. Status : " . $server_output['msg']);
            }
        }
        else
        {
            Mage::log(('Invoice #' . $magentoInvoice->getIncrementId() . ' failed because calling curl failed'), null, $proforma_create_new_log_file);
            throw new Exception("Failed to create proforma invoice. Status : Invoice #" . $magentoInvoice->getIncrementId() . " failed because calling curl failed");
        }
    }

    protected function processOld(RocketWeb_Netsuite_Model_Queue_Message $message, $queueData = array (), $magentoOrder, $magentoInvoice) {

        $netsuiteService = Mage::helper('rocketweb_netsuite')->getNetsuiteService();

        $initializeObject = new InitializeRecord();
        $initializeObject->reference = new InitializeRef();
        $initializeObject->reference->type = RecordType::salesOrder;
        $initializeObject->reference->internalId = $magentoOrder->getNetsuiteInternalId();
        
        if ($this->isNetsuiteCashSaleType()) {
            $initializeObject->type = RecordType::cashSale;
        }
        else {
            $initializeObject->type = RecordType::invoice;
        }
        
        $initializeRequest = new InitializeRequest();
        $initializeRequest->initializeRecord = $initializeObject;
        $initializeResponse = $netsuiteService->initialize($initializeRequest);

        if ($initializeResponse->readResponse->status->isSuccess) {
            $cashSale = $initializeResponse->readResponse->record;
            
            if ($this->isNetsuiteCashSaleType()) {
                $cashSale = Mage::helper('rocketweb_netsuite/mapper_invoice')->cleanupNetsuiteCashSale($cashSale,$magentoInvoice);
            }
            else {
                $cashSale = Mage::helper('rocketweb_netsuite/mapper_invoice')->cleanupNetsuiteInvoice($cashSale,$magentoInvoice);
            }

            foreach ($cashSale->itemList->item as &$item) {
                unset ($item->taxRate1);
            }

            $request = new AddRequest();
            $request->record = $cashSale;
            $response = $netsuiteService->add($request);

            if ($response->writeResponse->status->isSuccess) {
                $netsuiteId = $response->writeResponse->baseRef->internalId;
                $magentoInvoice->setNetsuiteInternalId($netsuiteId);
                $magentoInvoice->getResource()->save($magentoInvoice);
            }
            else {
                throw new Exception((string) print_r($response->writeResponse->status->statusDetail, true));
            }

            if ($this->isNetsuiteInvoiceType()) {
                //we also need a payment record
                $initializeRequest = new InitializeRequest();
                $initializeObject = new InitializeRecord();
                $initializeObject->reference = new InitializeRef();
                $initializeObject->reference->type = RecordType::invoice;
                $initializeObject->reference->internalId = $netsuiteId; //the invoice id
                $initializeObject->type = RecordType::customerPayment;

                $initializeRequest = new InitializeRequest();
                $initializeRequest->initializeRecord = $initializeObject;
                $initializeResponse = $netsuiteService->initialize($initializeRequest);

                if ($initializeResponse->readResponse->status->isSuccess) {
                    /* @var CustomerPayment $customerPayment */
                    $customerPayment = $initializeResponse->readResponse->record;

                    $request = new AddRequest();
                    $request->record = $customerPayment;
                    $response = $netsuiteService->add($request);

                    if ($response->writeResponse->status->isSuccess) {
                        //$netsuiteId = $response->writeResponse->baseRef->internalId;
                        //throw new Exception("Invoice #{$message->getEntityId()} exit process manually!");
                        return;

                    }
                    else {
                        throw new Exception((string) print_r($response->writeResponse->status->statusDetail, true));
                    }
                }
                else {
                    throw new Exception((string) print_r($initializeResponse->readResponse->status->statusDetail, true));
                }
            }
        }
        else {
            throw new Exception((string) print_r($initializeResponse->readResponse->status->statusDetail, true));
        }
    }

    protected function isNetsuiteInvoiceType() {
        if (Mage::helper('rocketweb_netsuite')->getInvoiceTypeInNetsuite() == RocketWeb_Netsuite_Model_Adminhtml_System_Config_Source_Invoicenetsuitetype::TYPE_INVOICE) {
            return true;
        }
        else {
            return false;
        }
    }

    protected function isNetsuiteCashSaleType() {
        return !$this->isNetsuiteInvoiceType();
    }
    
    protected function createMagentoInvoiceLog($queueData) {
        $today = date('Ymd');
        $path = "netsuite/export/invoice/{$today}/";
        $filename = $queueData['message_id'];
        $content = json_encode($queueData);
        $fullpath = '';
        $pathArr = explode('/', $path);

        if (is_array($pathArr)) {
            foreach ($pathArr as $key => $value) {
                if (empty ($value)) {
                    continue;
                }
                
                // check folder exist
                $foldername = empty ($fullpath) ? $value : $fullpath . $value;
                
                if (!file_exists($this->getMagentoBaseDir() . $foldername)) {
                    mkdir($this->getMagentoBaseDir() . $foldername, 0777, true);
                }
                
                $fullpath .= $value . "/";
            }
        }
        
        $fullFilename = $this->getMagentoBaseDir() . $fullpath . $filename;
        
        if (!file_exists($fullFilename)) {
            $handle = fopen($fullFilename, 'w');
            fwrite($handle, $content . "\n");
            fclose($handle);
        }
    }
    
    protected function getMagentoBaseDir() {
        return Mage::getBaseDir() . "/var/log/";
    }
}