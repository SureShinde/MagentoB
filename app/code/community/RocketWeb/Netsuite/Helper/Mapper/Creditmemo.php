<?php
/**
 * Description of RocketWeb_Netsuite_Helper_Mapper_Creditmemo
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class RocketWeb_Netsuite_Helper_Mapper_Creditmemo extends RocketWeb_Netsuite_Helper_Mapper {
    public function getMagentoFormat(CreditMemo $creditmemo)
    {
        $magentoIncrementId = $this->getIncrementIdFromNetsuite($creditmemo);
        $magentoOrder = Mage::getModel('sales/order')->loadByIncrementId($magentoIncrementId);
        $magentoConvertor = Mage::getModel('sales/convert_order');
        
        if (!$magentoOrder->getId()) {
            throw new Exception("Credit Memo with Order IncrementID #{$magentoIncrementId} not found!");
        }
        
        $orderId = $magentoOrder->getId();

        if($magentoOrder->hasInvoices())
        {
            $invoiceObjects = $magentoOrder->getInvoiceCollection();
            $invoiceObj = $invoiceObjects->getFirstItem();
            $invoice_id = $invoiceObj['entity_id'];

            $mgOrder = array();
            foreach($magentoOrder->getAllItems() as $item)
            {
                $mgOrder[$item->getData('item_id')]['productType'] = $item->getProductType();
                $mgOrder[$item->getData('item_id')]['parentItemId'] = $item->getData('parent_item_id');
            }         

            $nsCreditmemo = array();
            foreach ($creditmemo->itemList->item as $netsuiteItem)
            { 
                $itemId =$itemParentId = null;
                foreach ($netsuiteItem->customFieldList->customField as $customField)
                {
                    if ($customField->internalId == 'custcol_magentoitemid')
                    {
                        $itemId =$customField->value;
                        //$nsCreditmemo[$itemId]['itemId'] = $itemId;
                    }
                    if ($customField->internalId == 'custcol_parentid')
                    {
                        $itemParentId =$customField->value;
                        //$nsCreditmemo[$itemId]['parentItemId'] = $itemParentId;
                    }


                }

                if( !is_null($itemParentId) && $mgOrder[$itemParentId]['productType'] == 'bundle' )
                {
                    $items[$itemId]['back_to_stock']  = 1;//number_format($netsuiteItem->quantity,0);
                    $items[$itemId]['qty']            = number_format($netsuiteItem->quantity,0);
                }elseif(!is_null($itemParentId) && $mgOrder[$itemParentId]['productType'] == 'configurable')
                {
                    $items[$itemParentId]['back_to_stock']  = 1;//number_format($netsuiteItem->quantity,0);
                    $items[$itemParentId]['qty']            = number_format($netsuiteItem->quantity,0);
                }else{
                    $items[$itemId]['back_to_stock']  = 1;//number_format($netsuiteItem->quantity,0);
                    $items[$itemId]['qty']            = number_format($netsuiteItem->quantity,0);
                }
            }
            
        }
        $creditmemo = array(
            'items' =>  $items,
            'comment_text' => 'Create Creditmemo from Netsuite #' . $creditmemo->internalId,
            'comment_customer_notify' => 'Order Cancel is Successful',
            'shipping_amount' => $this->getRefundShippingFromNetsuite($creditmemo),
            'adjustment_positive' => $this->getAdjustmentPositiveFromNetsuite($creditmemo),
            'adjustment_negative' => $this->getAdjustmentNegativeFromNetsuite($creditmemo),
            'netsuite_internal_id'=> $creditmemo->internalId,
            'lastModifiedDate'=> $creditmemo->lastModifiedDate,
            'do_offline' => 'do_offline',
            'send_email' => 'YES'
        );

        $data=array(
            'order_id'           => $orderId,
            'order_increment_id' => $magentoOrder->getIncrementId(),
            'invoice_id'         => $invoice_id,
            'creditmemo'         => $creditmemo
        );

        $errorMessage = $this->validateCreditMemo($magentoOrder);
        if($errorMessage == '')
        {
            return $data;
        }

        return false;
    }
    
    protected function validateCreditMemo($order)
    {
        $errorMessage = '';
        if (!$order->canCreditmemo()) {
            $errorMessage = 'cannot create credit memo';
            if(!$order->isPaymentReview())
            {
                $errorMessage = 'cannot create credit memo Payment is in review';
            }
            if(!$order->canUnhold())
            {
                $errorMessage = 'cannot create credit memo Order is on hold';
            }
            if(abs($order->getTotalPaid()-$order->getTotalRefunded())<.0001)
            {
                $errorMessage = 'cannot create credit memo Amount Paid is equal or less than amount refunded';
            }
            if($order->getActionFlag('edit') === false)
            {
                $errorMessage = 'cannot create credit memo Action Flag of Edit not set';
            }
            if ($order->hasForcedCanCreditmemo()) {
                $errorMessage = 'cannot create credit memo Can Credit Memo has been forced set';
            }
        }
        return $errorMessage;
    }

    protected function getIncrementIdFromNetsuite(CreditMemo $creditmemo) {
        if ($creditmemo->customFieldList->customField) {
            foreach ($creditmemo->customFieldList->customField as $customField) {
                if ($customField->internalId == 'custbody_magento_order_id') {
                    return $customField->value;
                }
            }
        }
        
        return false;
    }

    protected function getRefundShippingFromNetsuite(CreditMemo $creditmemo)
    {
        if ($creditmemo->customFieldList->customField) {
            foreach ($creditmemo->customFieldList->customField as $customField) {
                if ($customField->internalId == 'custbody_refundshipping') {
                    return (int) $customField->value;
                }
            }
        }
        
        return 0;
    }   

    protected function getAdjustmentPositiveFromNetsuite(CreditMemo $creditmemo)
    {
        if ($creditmemo->customFieldList->customField) {
            foreach ($creditmemo->customFieldList->customField as $customField) {
                if ($customField->internalId == 'custbody_adjustmentrefund') {
                    return (int) $customField->value;
                }
            }
        }
        
        return 0;
    }

    protected function getAdjustmentNegativeFromNetsuite(CreditMemo $creditmemo)
    {
        if ($creditmemo->customFieldList->customField) {
            foreach ($creditmemo->customFieldList->customField as $customField) {
                if ($customField->internalId == 'custbody_adjustmentfee') {
                    return (int) $customField->value;
                }
            }
        }
        
        return 0;
    }
 
    protected function getCreditmemoCollectionFromNetsuite($magentoOrderId) {
        $collection = Mage::getModel('sales/order_creditmemo')->getCollection();
        $collection->addFieldToFilter('order_id', $magentoOrderId);
        
        return $collection->getFirstItem();
    }
}