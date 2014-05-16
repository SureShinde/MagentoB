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
        $orderCollection = $this->getOrderCollection();
        $this->writeLog("Processing {$orderCollection->getSize()} orders ...");
        
        foreach ($orderCollection as $order) {
            $magentoInvoice = Mage::getModel('sales/order_invoice')
                ->getCollection()
                ->addAttributeToFilter('order_id', $order->getEntityId())
                ->getFirstItem();
            
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
            $magentoOrder = $magentoInvoice->getOrder();
            
            $initializeObject = new InitializeRecord();
            $initializeObject->reference = new InitializeRef();
            $initializeObject->reference->type = RecordType::salesOrder;
            $initializeObject->reference->internalId = $magentoOrder->getNetsuiteInternalId();
            //$initializeObject->reference->internalId = $magentoOrder->getIncrementId();
            
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
    
    protected function getOrderCollection() {
        $orderCollection = Mage::getModel('sales/order')->getCollection();
        $orderCollection->addAttributeToFilter('netsuite_internal_id', array ('neq' => ''));
        $orderCollection->addAttributeToFilter('entity_id', array ('lteq' => $this->getMaxOrderId()));
        $orderCollection->addAttributeToSort('entity_id', 'DESC');
        $orderCollection->getSelect()->limit($this->getOrderCollectionLimit());

        return $orderCollection;
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
        return Mage::getStoreConfig('rocketweb_netsuite/exports/max_entity_id');
    }

    protected function getOrderCollectionLimit() {
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
