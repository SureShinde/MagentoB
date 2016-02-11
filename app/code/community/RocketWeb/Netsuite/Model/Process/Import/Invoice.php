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

class RocketWeb_Netsuite_Model_Process_Import_Invoice extends RocketWeb_Netsuite_Model_Process_Import_Abstract {
    public function getPermissionName() {
        return RocketWeb_Netsuite_Helper_Permissions::GET_CASH_SALES;
    }

    public function getRecordType() {
        return RecordType::invoice;
    }

    public function isActive() {
        if (Mage::helper('rocketweb_netsuite')->getInvoiceTypeInNetsuite() == RocketWeb_Netsuite_Model_Adminhtml_System_Config_Source_Invoicenetsuitetype::TYPE_INVOICE) {
            return true;
        }
        else {
            return false;
        }
    }

    public function getMessageType() {
        return RocketWeb_Netsuite_Model_Queue_Message::CASHSALE_IMPORTED;
    }

    public function getDeleteMessageType() {
        return RocketWeb_Netsuite_Model_Queue_Message::CASHSALE_DELETED;
    }
    
    public function process(Record $invoice, $queueData = NULL) {
        $magentoInvoice = Mage::helper('rocketweb_netsuite/mapper_invoice')->getMagentoFormatFromInvoice($invoice);
        /*$magentoInvoice->setNetsuiteInternalId($invoice->internalId);
        $magentoInvoice->setLastImportDate(Mage::helper('rocketweb_netsuite')->convertNetsuiteDateToSqlFormat($invoice->lastModifiedDate));
        $magentoInvoice->save();*/
    }

    public function processOld(Record $invoice) {
        $magentoInvoice = Mage::helper('rocketweb_netsuite/mapper_invoice')->getMagentoFormatFromInvoice($invoice);
        $existingInvoice = $this->getExistingInvoice($invoice);
        
        if ($existingInvoice) {
            foreach ($existingInvoice->getAllItems() as $item) {
                $item->delete();
            }
            
            $magentoInvoice->setId($existingInvoice->getId());
        }

        $magentoInvoice->setNetsuiteInternalId($invoice->internalId);
        $magentoInvoice->setLastImportDate(Mage::helper('rocketweb_netsuite')->convertNetsuiteDateToSqlFormat($invoice->lastModifiedDate));

        if (!$magentoInvoice->getCommentsCollection()->count()) {
            //we only want to add an auto-comment when the shipment is created, i.e. when there are no comments
            $magentoInvoice->addComment("Imported from Net Suite - invoice transaction id #{$invoice->tranId}", false, false);
        }

        Mage::register('skip_invoice_export_queue_push', 1);
        $magentoInvoice->collectTotals();
        $magentoInvoice->save();

        $this->updatePrices($magentoInvoice, $invoice);
    }

    protected function getExistingInvoice($record) {
        $invoiceCollection = Mage::getModel('sales/order_invoice')->getCollection();
        $invoiceCollection->addFieldToFilter('netsuite_internal_id', $record->internalId);
        
        if ($invoiceCollection->count()) {
            return $invoiceCollection->getFirstItem();
        }
        else {
            return null;
        }
    }

    /**
     * Force price updates. Magento does not allow changing the prices in the invoices, but Netsuite does.
     */
    protected function updatePrices(Mage_Sales_Model_Order_Invoice $magentoInvoice, Invoice $invoice) {
        $grandSubtotal = 0;
        $grandTax = 0;
        $grandTotal = 0;

        foreach ($magentoInvoice->getAllItems() as $magentoInvoiceItem) {
            $productInternalNetsuiteId = Mage::getModel('catalog/product')->load($magentoInvoiceItem->getOrderItem()->getProductId())->getNetsuiteInternalId();
            
            foreach ($invoice->itemList->item as $netsuiteItem) {
                if ($productInternalNetsuiteId && $netsuiteItem->item->internalId == $productInternalNetsuiteId) {
                    //$magentoInvoiceItem->setPrice($netsuiteItem->amount / $netsuiteItem->quantity);
                    //$magentoInvoiceItem->setRowTotal($netsuiteItem->amount);
                    //$magentoInvoiceItem->setTaxAmount(round($netsuiteItem->amount / 100 * $netsuiteItem->taxRate1, 2));
                    //$magentoInvoiceItem->save();

                    $grandTax += $magentoInvoiceItem->getTaxAmount();
                    $grandSubtotal += $magentoInvoiceItem->getRowTotal();
                    $grandTotal += $grandTax + $grandSubtotal;
                }
            }
        }

        //$magentoInvoice->setTaxAmount($grandTax);
        //$magentoInvoice->setSubtotal($grandSubtotal);
        //$magentoInvoice->setGrandTotal($invoice->total);
        $magentoInvoice->getResource()->save($magentoInvoice);

        // update the invoice grid
        //$dbConnection = Mage::getSingleton('core/resource')->getConnection('core_write');
        //$tableName = Mage::getSingleton('core/resource')->getTableName('sales_flat_invoice_grid');
        //$query = "UPDATE $tableName SET grand_total={$invoice->total}, base_grand_total={$invoice->total} WHERE entity_id = {$magentoInvoice->getId()}";
        //$dbConnection->query($query);
    }

    public function isAlreadyImported(SearchRow $record) {
        $shipmentCollection = Mage::getModel('sales/order_invoice')->getCollection();
        $shipmentCollection->addFieldToFilter('netsuite_internal_id', $record->basic->internalId[0]->searchValue->internalId);
        $netsuiteUpdateDatetime = Mage::helper('rocketweb_netsuite')->convertNetsuiteDateToSqlFormat($record->basic->lastModifiedDate[0]->searchValue);
        $shipmentCollection->addFieldToFilter('last_import_date', array ('gteq' => $netsuiteUpdateDatetime));
        $shipmentCollection->load();
        
        if ($shipmentCollection->count()) {
            return true;
        }
        else {
            return false;
        }
    }

    public function isAlreadyImportedOld(Record $record) {
        $shipmentCollection = Mage::getModel('sales/order_invoice')->getCollection();
        $shipmentCollection->addFieldToFilter('netsuite_internal_id', $record->internalId);
        $netsuiteUpdateDatetime = Mage::helper('rocketweb_netsuite')->convertNetsuiteDateToSqlFormat($record->lastModifiedDate);
        $shipmentCollection->addFieldToFilter('last_import_date', array ('gteq' => $netsuiteUpdateDatetime));
        $shipmentCollection->load();
        
        if ($shipmentCollection->count()) {
            return true;
        }
        else {
            return false;
        }
    }

    //check if an order with the item fullfilment's createFrom internalId exists in Magento. If not, the record is not for a Magento order
    public function isMagentoImportable($invoice) {
        if (is_null($invoice->basic->createdFrom)) {
            return false;
        }
        
        $netsuiteOrderId = $invoice->basic->createdFrom[0]->searchValue->internalId;
        $magentoOrders = Mage::getModel('sales/order')->getCollection()->addFieldToFilter('netsuite_internal_id', $netsuiteOrderId);
        $magentoOrders->load();
        
        if (!$magentoOrders->getSize()) {
            return false;
        }
        else {
            return true;
        }
    }

    //check if an order with the item fullfilment's createFrom internalId exists in Magento. If not, the record is not for a Magento order
    public function isMagentoImportableOld(Record $invoice) {
        if (is_null($invoice->createdFrom)) {
            return false;
        }
        
        $netsuiteOrderId = $invoice->createdFrom->internalId;
        $magentoOrders = Mage::getModel('sales/order')->getCollection()->addFieldToFilter('netsuite_internal_id', $netsuiteOrderId);
        $magentoOrders->load();
        
        if (!$magentoOrders->getSize()) {
            return false;
        }
        else {
            return true;
        }
    }
}