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

class RocketWeb_Netsuite_Model_Process_Import_Cashsale extends RocketWeb_Netsuite_Model_Process_Import_Abstract {

    public function getPermissionName() {
        return RocketWeb_Netsuite_Helper_Permissions::GET_CASH_SALES;
    }

    public function getRecordType() {
        return RecordType::cashSale;
    }

    public function getMessageType() {
        return RocketWeb_Netsuite_Model_Queue_Message::CASHSALE_IMPORTED;
    }

    public function getDeleteMessageType() {
        return RocketWeb_Netsuite_Model_Queue_Message::CASHSALE_DELETED;
    }

    public function process(Record $cashSale) {
        $magentoInvoice = Mage::helper('rocketweb_netsuite/mapper_invoice')->getMagentoFormatFromCashSale($cashSale);
        
        if ($magentoInvoice) {
            $existingInvoice = $this->getExistingInvoice($cashSale);

            if ($existingInvoice) {
                foreach ($existingInvoice->getAllItems() as $item) {
                    $item->delete();
                }

                $magentoInvoice->setId($existingInvoice->getId());
            }

            $magentoInvoice->setNetsuiteInternalId($cashSale->internalId);
            $magentoInvoice->setLastImportDate(Mage::helper('rocketweb_netsuite')->convertNetsuiteDateToSqlFormat($cashSale->lastModifiedDate));

            if (!$magentoInvoice->getCommentsCollection()->count()) {
                //we only want to add an auto-comment when the shipment is created, i.e. when there are no comments
                $magentoInvoice->addComment("Imported from Netsuite - cashSale transactionId #{$cashSale->tranId}", false, false);
            }

            Mage::register('skip_invoice_export_queue_push', 1);
            $magentoInvoice->collectTotals();
            $magentoInvoice->save();

            $this->updatePrices($magentoInvoice, $cashSale);
        }
    }

    //check if an order with the item fullfilment's createFrom internalId exists in Magento. If not, the record is not for a Magento order
    public function isMagentoImportable(Record $invoice) {
        if(is_null($invoice->createdFrom)) {
            return false;
        }
        $netsuiteOrderId = $invoice->createdFrom->internalId;
        $magentoOrders = Mage::getModel('sales/order')->getCollection()->addFieldToFilter('netsuite_internal_id',$netsuiteOrderId);
        $magentoOrders->load();
        if(!$magentoOrders->getSize()) {
            return false;
        }
        else {
            return true;
        }
    }

    public function isAlreadyImported(Record $record) {
        $shipmentCollection = Mage::getModel('sales/order_invoice')->getCollection();
        $shipmentCollection->addFieldToFilter('netsuite_internal_id',$record->internalId);
        $netsuiteUpdateDatetime = Mage::helper('rocketweb_netsuite')->convertNetsuiteDateToSqlFormat($record->lastModifiedDate);
        $shipmentCollection->addFieldToFilter('last_import_date',array('gteq'=>$netsuiteUpdateDatetime));
        $shipmentCollection->load();
        if($shipmentCollection->count()) {
            return true;
        }
        else {
            return false;
        }
    }

    public function isActive() {
        if(Mage::helper('rocketweb_netsuite')->getInvoiceTypeInNetsuite() == RocketWeb_Netsuite_Model_Adminhtml_System_Config_Source_Invoicenetsuitetype::TYPE_CASH_SALE) {
            return true;
        }
        else {
            return false;
        }
    }

    protected function getExistingInvoice($record) {
        $invoiceCollection = Mage::getModel('sales/order_invoice')->getCollection();
        $invoiceCollection->addFieldToFilter('netsuite_internal_id',$record->internalId);
        if($invoiceCollection->count()) {
            return $invoiceCollection->getFirstItem();
        }
        else {
            return null;
        }
    }

    /*
     * Force price updates. Magento does not allow changing the prices in the invoices, but Net Suite does.
     */
    protected function updatePrices(Mage_Sales_Model_Order_Invoice $magentoInvoice, CashSale $cashSale) {
        $grandSubtotal = 0;
        $grandTax = 0;
        $grandTotal = 0;
        $totalQty = 0;
        $bilnaCredit = 0;

        foreach ($magentoInvoice->getAllItems() as $magentoInvoiceItem) {
            $productInternalNetsuiteId = Mage::getModel('catalog/product')->load($magentoInvoiceItem->getOrderItem()->getProductId())->getNetsuiteInternalId();
            
            foreach ($cashSale->itemList->item as $netsuiteItem) {
                if ($productInternalNetsuiteId && $netsuiteItem->item->internalId == $productInternalNetsuiteId) {
                    $_netsuitePrice = $this->getNetsuitePrice($netsuiteItem->customFieldList->customField);
                    $_netsuiteRowTotal = $_netsuitePrice * $netsuiteItem->quantity;
                    
                    //$_netsuiteSubTotal += $_netsuiteRowTotal;
                    $magentoInvoiceItem->setPrice($_netsuitePrice);
                    $magentoInvoiceItem->setRowTotal($_netsuiteRowTotal);
                    $magentoInvoiceItem->setTaxAmount(round($_netsuiteRowTotal / 100 * $netsuiteItem->taxRate1, 2));
                    $magentoInvoiceItem->getOrderItem()->setQtyInvoiced($netsuiteItem->quantity);
                    $magentoInvoiceItem->save();

                    $grandTax += $magentoInvoiceItem->getTaxAmount();
                    $grandSubtotal += $magentoInvoiceItem->getRowTotal();
                    $grandTotal += $grandTax + $grandSubtotal;
                    $totalQty += $netsuiteItem->quantity;
                    $bilnaCredit += $this->getNetsuiteBilnaCredit($netsuiteItem->customFieldList->customField);
                }
            }
        }

        $magentoInvoice->setTotalQty($totalQty);
        $magentoInvoice->setTaxAmount($grandTax);
        $magentoInvoice->setBaseTaxAmount($grandTax);
        $magentoInvoice->setSubtotal($grandSubtotal);
        $magentoInvoice->setBaseSubtotal($grandSubtotal);
        $magentoInvoice->setGrandTotal($cashSale->total);
        $magentoInvoice->setBaseGrandTotal($cashSale->total);
        $magentoInvoice->setMoneyForPoints($bilnaCredit);
        $magentoInvoice->setBaseMoneyForPoints($bilnaCredit);
        $magentoInvoice->getOrder()->setTotalPaid($magentoInvoice->getOrder()->getTotalPaid() + $magentoInvoice->getGrandTotal());
        $magentoInvoice->getOrder()->setBaseTotalPaid($magentoInvoice->getOrder()->getBaseTotalPaid() + $magentoInvoice->getBaseGrandTotal());
        $magentoInvoice->getOrder()->save();
        $magentoInvoice->getResource()->save($magentoInvoice);
        $magentoInvoice->sendEmail(true, '');
        
        //update the invoice grid
        $dbConnection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tableName = Mage::getSingleton('core/resource')->getTableName('sales_flat_invoice_grid');
        $query = "UPDATE $tableName SET grand_total = {$cashSale->total}, base_grand_total = {$cashSale->total} WHERE entity_id = {$magentoInvoice->getId()}";
        $dbConnection->query($query);
    }
    
    protected function getNetsuitePrice($itemCustomField) {
        $result = 0;
        
        foreach ($itemCustomField as $item) {
            if ($item->internalId == 'custcol_pricebeforediscount') {
                $result = $item->value;
                break;
            }
        }
        
        return $result;
    }
    
    protected function getNetsuiteBilnaCredit($itemCustomField) {
        $result = 0;
        
        foreach ($itemCustomField as $item) {
            if ($item->internalId == 'custcol_bilnacredit') {
                $result = $item->value;
                break;
            }
        }
        
        return $result;
    }
}