<?php
/**
 * Description of RocketWeb_Netsuite_Model_Process_Import_Creditmemo
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class RocketWeb_Netsuite_Model_Process_Import_Creditmemo extends RocketWeb_Netsuite_Model_Process_Import_Abstract {
    public function isMagentoImportable(Record $record) {
        return true;
        
        if (is_null($record->createdFrom)) {
            return false;
        }
        
        $netsuiteInternalId = $record->internalId;
        $magentoCreditmemos = Mage::getModel('sales/order_creditmemo')->getCollection()->addFieldToFilter('netsuite_internal_id', $netsuiteInternalId);
        $magentoCreditmemos->load();
        
        if (!$magentoCreditmemos->getSize()) {
            return false;
        }
        else {
            return true;
        }
    }
    
    public function getMessageType() {
        return RocketWeb_Netsuite_Model_Queue_Message::CREDITMEMO_IMPORTED;
    }
    
    public function getDeleteMessageType() {
        return RocketWeb_Netsuite_Model_Queue_Message::CREDITMEMO_DELETED;
    }
    
    public function process(Record $record) {
        $magentoCreditmemo = Mage::helper('rocketweb_netsuite/mapper_creditmemo')->getMagentoFormat($record);
        $magentoCreditmemo->setNetsuiteInternalId($record->internalId);
        $magentoCreditmemo->setLastImportDate(Mage::helper('rocketweb_netsuite')->convertNetsuiteDateToSqlFormat($record->lastModifiedDate));
        $magentoCreditmemo->collectTotals();
        $magentoCreditmemo->save();
        //$magentoCreditmemo->getResource()->save($magentoCreditmemo);
        
        
        $this->updatePrices($magentoCreditmemo, $record);
    }
    
    protected function updatePrices(Mage_Sales_Model_Order_Creditmemo $magentoCreditmemo, CreditMemo $netsuiteCreditmemo) {
        $grandSubtotal = 0;
        $grandTax = 0;
        $grandTotal = 0;
        $totalQty = 0;
        $bilnaCredit = 0;

//error_log("\nmagentoCreditMemo".print_r($magentoCreditmemo,1),3,'/tmp/netCreditMemo.log');        
error_log("\nnetsuiteCreditMemo".print_r($netsuiteCreditmemo,1),3,'/tmp/netCreditMemo6.log');        
        foreach ($magentoCreditmemo->getAllItems() as $magentoCreditmemoItem) {
            $productInternalNetsuiteId = Mage::getModel('catalog/product')->load($magentoCreditmemoItem->getOrderItem()->getProductId())->getNetsuiteInternalId();
//error_log("\nmagentoCreditMemoItem".print_r($magentoCreditmemoItem,1),3,'/tmp/netCreditMemo.log');        
            
            foreach ($netsuiteCreditmemo->itemList->item as $netsuiteItem) {
                if ($productInternalNetsuiteId && $netsuiteItem->item->internalId == $productInternalNetsuiteId) {
                    $_price = $magentoCreditmemoItem->getPrice();
                    $_quantity = $netsuiteItem->quantity;
                    $_rowTotal = $_price * $_quantity;
                    $_discount = ($_price - $netsuiteItem->rate) * $_quantity;

error_log("\nprice ".print_r($_price,1),3,'/tmp/netCreditMemo.log');        
error_log("\nquantity ".print_r($_quantity,1),3,'/tmp/netCreditMemo.log');        
error_log("\nrowTotal ".print_r($_rowTotal,1),3,'/tmp/netCreditMemo.log');        
error_log("\ndiscount ".print_r($_discount,1),3,'/tmp/netCreditMemo.log');        
                    
                    $magentoCreditmemoItem->setQty($_quantity);
                    $magentoCreditmemoItem->setDiscountAmount($_discount);
                    $magentoCreditmemoItem->setBaseDiscountAmount($_discount);
                    $magentoCreditmemoItem->setRowTotal($_rowTotal);
                    $magentoCreditmemoItem->setBaseRowTotal($_rowTotal);
                    $magentoCreditmemoItem->setTaxAmount(round($_rowTotal / 100 * $netsuiteItem->taxRate1, 2));
                    $magentoCreditmemoItem->getOrderItem()->setQtyRefunded($_quantity);
                    $magentoCreditmemoItem->save();

                    $grandTax += $magentoCreditmemoItem->getTaxAmount();
                    $grandSubtotal += $magentoCreditmemoItem->getRowTotal();
                    $grandTotal += $grandTax + $grandSubtotal;
                    $totalQty += $_quantity;
                    //$bilnaCredit += $this->getNetsuiteBilnaCredit($netsuiteItem->customFieldList->customField);
                }
            }
        }

        $magentoCreditmemo->setTotalQty($totalQty);
        $magentoCreditmemo->setTaxAmount($grandTax);
        $magentoCreditmemo->setBaseTaxAmount($grandTax);
        $magentoCreditmemo->setSubtotal($grandSubtotal);
        $magentoCreditmemo->setBaseSubtotal($grandSubtotal);
        $magentoCreditmemo->setGrandTotal($grandTotal);
        $magentoCreditmemo->setBaseGrandTotal($grandTotal);
        $magentoCreditmemo->setState(Mage_Sales_Model_Order_Creditmemo::STATE_REFUNDED);
        //$magentoInvoice->setMoneyForPoints($bilnaCredit);
        //$magentoInvoice->setBaseMoneyForPoints($bilnaCredit);
        //$magentoInvoice->getOrder()->setTotalPaid($magentoInvoice->getOrder()->getTotalPaid() + $magentoInvoice->getGrandTotal());
        //$magentoInvoice->getOrder()->setBaseTotalPaid($magentoInvoice->getOrder()->getBaseTotalPaid() + $magentoInvoice->getBaseGrandTotal());
        //$magentoCreditmemo->getOrder()->save();
        $_adjustmentRefund = $this->getAdjustmentRefund($netsuiteCreditmemo);
        $_adjustmentFee = $this->getAdjustmentFee($netsuiteCreditmemo);
        
        //$this->log("adjustmentRefund: " . $_adjustmentRefund);
        //$this->log("adjustmentFee: " . $_adjustmentFee);
        
        $magentoCreditmemo->setAdjustment($_adjustmentRefund * -1);
        $magentoCreditmemo->setBaseAdjustment($_adjustmentRefund * -1);
        $magentoCreditmemo->setAdjustmentPositive($_adjustmentRefund);
        $magentoCreditmemo->setBaseAdjustmentPositive($_adjustmentRefund);
        $magentoCreditmemo->setAdjustmentNegative($_adjustmentFee);
        $magentoCreditmemo->setBaseAdjustmentNegative($_adjustmentFee);
        
        //$magentoCreditmemo->getResource()->save($magentoCreditmemo);
        $magentoCreditmemo->getOrder()->save();
        $magentoCreditmemo->save();
        $magentoCreditmemo->sendEmail(true, '');
        
        // update the order grid
        $dbConnection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tableName = Mage::getSingleton('core/resource')->getTableName('sales_flat_creditmemo_grid');
        $query = "UPDATE $tableName SET grand_total = {$netsuiteCreditmemo->total}, base_grand_total = {$netsuiteCreditmemo->total} WHERE entity_id = {$magentoCreditmemo->getId()}";
        //$this->log("queryCreditMemo: " . $query);
        $dbConnection->query($query);
    }

    public function getRecordType() {
        return RecordType::creditMemo;
    }

    public function isActive() {
        return true;
    }

    public function getPermissionName() {
        return RocketWeb_Netsuite_Helper_Permissions::GET_CREDITMEMO;
    }
    
    public function isAlreadyImported(Record $record) {
        return false;
        
        $creditmemoCollection = Mage::getModel('sales/order_creditmemo')->getCollection();
        $creditmemoCollection->addFieldToFilter('netsuite_internal_id', $record->internalId);
        $netsuiteUpdateDatetime = Mage::helper('rocketweb_netsuite')->convertNetsuiteDateToSqlFormat($record->lastModifiedDate);
        $creditmemoCollection->addFieldToFilter('last_import_date', array ('gteq' => $netsuiteUpdateDatetime));
        $creditmemoCollection->load();
        
        if ($creditmemoCollection->count()) {
            return true;
        }
        else {
            return false;
        }
    }
    
    protected function getAdjustmentRefund(CreditMemo $creditmemo) {
        if (is_array($creditmemo->customFieldList->customField)) {
            foreach ($creditmemo->customFieldList->customField as $field) {
                if ($field->internalId == 'custbody_adjustmentrefund') {
                    return (int) $field->value;
                }
            }
        }
        
        return 0;
    }
    
    protected function getAdjustmentFee(CreditMemo $creditmemo) {
        if (is_array($creditmemo->customFieldList->customField)) {
            foreach ($creditmemo->customFieldList->customField as $field) {
                if ($field->internalId == 'custbody_adjustmentfee') {
                    return (int) $field->value;
                }
            }
        }
        
        return 0;
    }
}
