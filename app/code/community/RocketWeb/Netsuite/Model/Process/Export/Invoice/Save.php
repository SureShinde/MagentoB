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

        $netsuiteService = Mage::helper('rocketweb_netsuite')->getNetsuiteService();
        $magentoOrder = $magentoInvoice->getOrder();
        
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