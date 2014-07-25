<?php
/**
 * Description of RocketWeb_Netsuite_Helper_Mapper_Creditmemo
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class RocketWeb_Netsuite_Helper_Mapper_Creditmemo extends RocketWeb_Netsuite_Helper_Mapper {
    public function getMagentoFormat(CreditMemo $creditmemo) {
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
            foreach ($creditmemo->itemList->item as $netsuiteItem)
            {
                foreach ($magentoOrder->getAllItems() as $magentoOrderItem)
                {
                    $netId = Mage::getModel('catalog/product')->load($magentoOrderItem->getProductId())->getNetsuiteInternalId();
                    if ( $netsuiteItem->item->internalId == $netId )
                    {
                        $items[$magentoOrderItem->getData('item_id')]['item_id']        = $magentoOrderItem->getData('item_id');
                        $items[$magentoOrderItem->getData('item_id')]['back_to_stock']  = number_format($netsuiteItem->quantity,0);
                        $items[$magentoOrderItem->getData('item_id')]['qty']            = number_format($netsuiteItem->quantity,0);

                    }

                }
            }
        }
        $creditmemo = array(
            'items' =>  $items,
            'comment_text' => 'Customer canceled the order',
            'comment_customer_notify' => 'Order Cancel is Successful',
            'shipping_amount' => 0,
            'adjustment_positive' => 0,
            'adjustment_negative' => 0,
            'do_offline' => 'do_offline',
            'send_email' => 'YES'
        );

        $data=array(
            'order_increment_id' => $magentoOrder->getIncrementId(),
            'invoice_id'         => $invoice_id,
            'creditmemo'         => $creditmemo
        );

        $errorMessage = $this->validateCreditMemo($magentoOrder);
        if($errorMessage == '')
        {
            //$details  = $this->initiateCreditMemo($data);
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
    
    protected function getCreditmemoCollectionFromNetsuite($magentoOrderId) {
        $collection = Mage::getModel('sales/order_creditmemo')->getCollection();
        $collection->addFieldToFilter('order_id', $magentoOrderId);
        
        return $collection->getFirstItem();
    }
}