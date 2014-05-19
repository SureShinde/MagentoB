<?php
/**
 * Description of Bilna_Netsuitesync_Shell_NetsuiteExportInvoice
 *
 * @author Bilna Development Team <development@bilna.com>
 */

require_once dirname(__FILE__) . '/../abstract.php';

class Bilna_Netsuitesync_Shell_NetsuiteExportInvoice extends Mage_Shell_Abstract {
    public function run() {
        $this->writeLog("Started export invoices ...");
        $invoiceCollection = $this->getInvoiceCollection();
        $this->writeLog("Processing {$invoiceCollection->getSize()} invoices ...");
        
        foreach ($invoiceCollection as $invoice) {
            $magentoInvoice = Mage::getModel('sales/order_invoice')->load($invoice->getEntityId());
            
            if (!$magentoInvoice->getEntityId()) {
                $this->writeLog("Cannot load invoice with id #{$magentoInvoice->getEntityId()} from Magento!");
                //throw new Exception("Cannot load invoice with id #{$magentoInvoice->getEntityId()} from Magento!");
                continue;
            }
            
            if ($magentoInvoice->getNetsuiteInternalId()) {
                $this->writeLog("Invoice #{$magentoInvoice->getEntityId()} has been exported.");
                continue;
            }
            
            $netsuiteService = Mage::helper('rocketweb_netsuite')->getNetsuiteService();
            $magentoOrder = Mage::getModel('sales/order')->load($magentoInvoice->getOrderId());
            
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
            
            $this->writeLog("requestInitialize: " . json_encode($initializeRequest));
            $this->writeLog("responseInitialize: " . json_encode($initializeResponse));

            if ($initializeResponse->readResponse->status->isSuccess) {
                $cashSale = $initializeResponse->readResponse->record;

                if ($this->isNetsuiteCashSaleType()) {
                    $cashSale = Mage::helper('rocketweb_netsuite/mapper_invoice')->cleanupNetsuiteCashSale($cashSale, $magentoInvoice);
                }
                else {
                    $cashSale = Mage::helper('rocketweb_netsuite/mapper_invoice')->cleanupNetsuiteInvoice($cashSale, $magentoInvoice);
                }

                foreach ($cashSale->itemList->item as &$item) {
                    unset ($item->taxRate1);
                }

                $request = new AddRequest();
                $request->record = $cashSale;
                $response = $netsuiteService->add($request);
                $this->writeLog("requestCashSale: " . json_encode($request));
                $this->writeLog("responseCashSale: " . json_encode($response));

                if ($response->writeResponse->status->isSuccess) {
                    $netsuiteId = $response->writeResponse->baseRef->internalId;
                    $magentoInvoice->setNetsuiteInternalId($netsuiteId);
                    $magentoInvoice->getResource()->save($magentoInvoice);
                }
                else {
                    $this->writeLog(json_encode($response->writeResponse->status->statusDetail));
                    continue;
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
                        $this->writeLog("requestCustomerPayment: " . json_encode($request));
                        $this->writeLog("responseCustomerPayment: " . json_encode($response));

                        if ($response->writeResponse->status->isSuccess) {
                            //$this->writeLog("response: " . $response->writeResponse->status->isSuccess);
                            continue;
                        }
                        else {
                            $this->writeLog(json_encode($response->writeResponse->status->statusDetail));
                            continue;
                        }
                    }
                    else {
                        $this->writeLog(json_encode($response->writeResponse->status->statusDetail));
                        continue;
                    }
                }
            }
            else {
                $this->writeLog(json_encode($response->writeResponse->status->statusDetail));
                continue;
            }
        }
        
        $this->writeLog("Ended export invoices ...");
    }
    
    protected function getInvoiceCollection() {
        $invoiceCollection = Mage::getModel("sales/order_invoice")->getCollection();
        $invoiceCollection->addAttributeToFilter('netsuite_internal_id', array ('eq' => ''));
        
        if ($this->getMaxOrderId()) {
            $invoiceCollection->addAttributeToFilter('entity_id', array ('lteq' => $this->getMaxOrderId()));
        }
        
        $invoiceCollection->addAttributeToSort('entity_id', 'DESC');
        $invoiceCollection->getSelect()->limit($this->getInvoiceCollectionLimit());

        return $invoiceCollection;
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
    
    protected function getMaxOrderId() {
        return Mage::getStoreConfig('rocketweb_netsuite/exports/max_invoice_entity_id');
    }

    protected function getInvoiceCollectionLimit() {
        return (int) Mage::getStoreConfig('rocketweb_netsuite/exports/limit');
    }
    
    protected function writeLog($message) {
        $filename = "netsuite.invoice." . date('Ymd', Mage::getModel('core/date')->timestamp(time())) . ".log";
        $datetime = date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(time()));
        @error_log("{$datetime} | {$message}\n", 3, "/usr/share/nginx/html/bilna.2014.new/var/log/netsuite/invoice/{$filename}");
    }
}

$shell = new Bilna_Netsuitesync_Shell_NetsuiteExportInvoice();
$shell->run();
