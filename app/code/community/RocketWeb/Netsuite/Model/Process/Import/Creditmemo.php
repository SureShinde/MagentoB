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
    
    public function process(Record $record, $queueData = null)
    {
        $data = Mage::helper('rocketweb_netsuite/mapper_creditmemo')->getMagentoFormat($record);
        if(!$data) return false;
        $details  = $this->initiateCreditMemo($data);
    }
    
    public function initiateCreditMemo($data = array())
    {
        $tempData = $data;
        $errorMessage = '';
        $creditMemoId = 0;
        try {
            $creditmemo = $this->_initCreditmemo($tempData);
            if ($creditmemo){
                if (($creditmemo->getGrandTotal() <=0) && (!$creditmemo->getAllowZeroGrandTotal())) {
                    $errorMessage = 'cannot create credit memo order total is zero.';
                }
                $comment = '';
                if (!empty($data['creditmemo']['comment_text'])) {
                    $creditmemo->addComment($data['creditmemo']['comment_text'], isset($data['creditmemo']['comment_customer_notify']));
                    if (isset($data['creditmemo']['comment_customer_notify'])) {
                        $comment = $data['creditmemo']['comment_text'];
                    }
                }
                if (isset($data['creditmemo']['do_refund'])) {
                    $creditmemo->setRefundRequested(true);
                }
                if (isset($data['creditmemo']['do_offline'])) {
                    $creditmemo->setOfflineRequested((bool)(int)$data['creditmemo']['do_offline']);
                }
                $creditmemo->register();
                if (!empty($data['creditmemo']['send_email'])) {
                    $creditmemo->setEmailSent(true);
                }
                $creditmemo->getOrder()->setCustomerNoteNotify(!empty($data['creditmemo']['send_email']));
                $creditMemoId = $this->_saveCreditmemo($creditmemo);
                $creditmemo->sendEmail(!empty($data['creditmemo']['send_email']), $comment);
            }
        } catch (Mage_Core_Exception $e) {
            $errorMessage = 'Unable to create a credit memo please try again later';
        }
        $details=array(
            'error_message'=>$errorMessage,
            'credit_memo_id'=>$creditMemoId
        );
        return $details;
    }

    protected function _initCreditmemo($tempData, $update = false)
    {
        $creditmemo       = false;
        $orderIncrementId = $tempData['order_increment_id'];
        $invoiceId        = $tempData['invoice_id'];
        $data             = $tempData['creditmemo'];
        $orderId          = $tempData['order_id'];
        //$order            = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);
        $order  = Mage::getModel('sales/order')->load($orderId);

        if ($invoiceId) {
            $invoice = Mage::getModel('sales/order_invoice')
                ->load($invoiceId)
                ->setOrder($order);
        }
        if(isset($data['items'])) {
            $savedData = $data['items'];
        } else { 
            $savedData = array();
        }

        $qtys = array();
        $backToStock = array();
        foreach ($savedData as $orderItemId =>$itemData) {
            if (isset($itemData['qty'])) {
                $qtys[$orderItemId] = $itemData['qty'];
            }
            if (isset($itemData['back_to_stock'])) {
                $backToStock[$orderItemId] = true;
            }
        }
        $data['qtys'] = $qtys;
        
        $totalBilnaCredit = 0;

        //$invoice = false;
        $service = Mage::getModel('sales/service_order', $order);
        if ($invoice) {
            $creditmemo = $service->prepareInvoiceCreditmemo($invoice, $data);
        } else {
            $creditmemo = $service->prepareCreditmemo($data);
        }

        /**
         * Process back to stock flags
         */
        foreach ($creditmemo->getAllItems() as $creditmemoItem) {
            $orderItem = $creditmemoItem->getOrderItem();
            $parentId = $orderItem->getParentItemId();
            if (isset($backToStock[$orderItem->getId()])) {
                $creditmemoItem->setBackToStock(true);
            } elseif ($orderItem->getParentItem() && isset($backToStock[$parentId]) && $backToStock[$parentId]) {
                $creditmemoItem->setBackToStock(true);
            } elseif (empty($savedData)) {
                $creditmemoItem->setBackToStock(Mage::helper('cataloginventory')->isAutoReturnEnabled());
            } else {
                $creditmemoItem->setBackToStock(false);
            }
        }
        $creditmemo->setNetsuiteInternalId($data['netsuite_internal_id']);
        $creditmemo->setLastImportDate(Mage::helper('rocketweb_netsuite')->convertNetsuiteDateToSqlFormat($data['lastModifiedDate']));
        return $creditmemo;

    }

    protected function _saveCreditmemo($creditmemo)
    {
        //$creditmemo->setTransactionId($TransactionNo);
        $transactionSave = Mage::getModel('core/resource_transaction')
            ->addObject($creditmemo)
            ->addObject($creditmemo->getOrder());
        if ($creditmemo->getInvoice()) {
            $transactionSave->addObject($creditmemo->getInvoice());
        }
        $transactionSave->save();
        //$creditmemo->save();
        //$creditMemoId = $creditmemo->getIncrementId();
        //Mage::log('Method :: _saveCreditmemo :: Credit Memo Id :: '.$creditMemoId);
        return true;//$creditMemoId;
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