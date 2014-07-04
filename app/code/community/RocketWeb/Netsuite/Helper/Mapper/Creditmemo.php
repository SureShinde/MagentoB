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
        
        $netsuiteCustomer = Mage::helper('rocketweb_netsuite/mapper_customer')->getByInternalId($magentoCreditmemo->entity->internalId);
        //$magentoCreditmemo = $magentoConvertor->toCreditmemo($magentoOrder);
        //$magentoCreditmemo->setOrder($magentoOrder)
        //$magentoCreditmemo->setStoreId($magentoOrder->getStoreId())->setCustomerId($magentoOrder->getCustomerId());
        
        //Mage::helper('core')->copyFieldset('sales_convert_order', 'to_creditmemo', $magentoOrder, $magentoCreditmemo);
        
        //$magentoCreditmemo->setBillingAddressId($magentoOrder->getBillingAddressId())->setShippingAddressId($magentoOrder->getShippingAddressId());
        $itemMap = array (); // save changes to quantity & prices for each order item
        
        foreach ($creditmemo->itemList->item as $netsuiteItem) {
            foreach ($magentoOrder->getAllItems() as $magentoOrderItem) {
                if ($netsuiteItem->item->internalId == Mage::getModel('catalog/product')->load($magentoOrderItem->getProductId())->getNetsuiteInternalId()) {
                    $item = array ();
                    $item['netsuite_object'] = $netsuiteItem;
                    $item['magento_orderitem_object'] = $magentoOrderItem;
                    $itemMap[] = $item;
                }
            }
        }
        
        $magentoService = Mage::getModel('sales/service_order', $magentoOrder);
        $data = array ();
        $qtys = array ();
        
        //$magentoCreditmemo = $magentoConvertor->toCreditmemo($magentoOrder);
        //$totalQuantity = 0;
        //$refundQty = 0;
        
        if (is_array($itemMap)) {
            foreach ($itemMap as $item) {
                // skip if item is wrapping
                if (preg_match('/^(\wrapping)/', $item['netsuite_object']->item->name)) {
                    continue;
                }
                
                $quantity = $item['netsuite_object']->quantity;
                $qtys[] = array ($item['magento_orderitem_object']->getId() => $quantity);
//                'qtys' => array(
//                    $orderItem->getId() => 1 //qty to refund.. $orderItem->getQty()
//                )
                
                //$quantity = $item['netsuite_object']->quantity;
                
                //$refundOrderItem = Mage::getModel('sales/order_item')->load($item['magento_orderitem_object']->getId());
                //$refundOrderItem = $magentoOrder->getItemsCollection()->getItemByColumnValue('sku', $item['netsuite_object']->item->description);
                //$magentoCreditmemoItem = $magentoConvertor->itemToCreditmemoItem($item['magento_orderitem_object']);
                //$magentoCreditmemoItem->setQty($quantity);
                //$magentoCreditmemoItem->register();
                //$magentoCreditmemoItem->setDiscountAmount($refundOi->getDiscountAmount() * ($magentoCreditmemoItem->getQty() / $magentoCreditmemoItem->getOrderItem()->getQtyOrdered()));
                //$magentoCreditmemoItem->setBaseDiscountAmount($refundOi->getBaseDiscountAmount() * ($magentoCreditmemoItem->getQty() / $magentoCreditmemoItem->getOrderItem()->getQtyOrdered()));
                //$magentoCreditmemoItem->setTaxAmount($refundOi->getTaxAmount() * ($magentoCreditmemoItem->getQty() / $magentoCreditmemoItem->getOrderItem()->getQtyOrdered()));
                //$magentoCreditmemoItem->setBaseTaxAmount($refundOi->getBaseTaxAmount() * ($magentoCreditmemoItem->getQty() / $magentoCreditmemoItem->getOrderItem()->getQtyOrdered()));
                //$magentoCreditmemoItem->setHiddenTaxAmount($refundOi->getHiddenTaxAmount() * ($magentoCreditmemoItem->getQty() / $magentoCreditmemoItem->getOrderItem()->getQtyOrdered()));
                //$magentoCreditmemoItem->setBaseHiddenTaxAmount($refundOi->getBaseHiddenTaxAmount() * ($magentoCreditmemoItem->getQty() / $magentoCreditmemoItem->getOrderItem()->getQtyOrdered()));

            
                //$magentoCreditmemoItem = Mage::getModel('sales/order_creditmemo_item');
                //Mage::helper('core')->copyFieldset('sales_convert_order_item', 'to_creditmemo_item', $item['magento_orderitem_object'], $magentoCreditmemoItem);
                //$magentoCreditmemoItem->setOrderItem($item['magento_orderitem_object']);
                //$magentoCreditmemoItem->setProductId(Mage::getModel('catalog/product')->load($item['magento_orderitem_object']->getProductId())->getId());
                ////intentionally using setData instead of ->setQty as at this point we dont care about out of stock exceptions
                //$magentoCreditmemoItem->setData('qty', $item['netsuite_object']->quantity);
                //$magentoCreditmemo->addItem($magentoCreditmemoItem);
                
                $totalQuantity += $quantity;
            }
        }
        
        $data[] = $qtys;
        $magentoCreditmemo = $magentoService->prepareCreditmemo($data)->save();
        
        //$magentoCreditmemo->addItem($magentoCreditmemoItem);
        //$magentoCreditmemo->register();

        //Mage::getModel('core/resource_transaction')
        //    ->addObject($magentoCreditmemo)
        //    ->addObject($magentoCreditmemo->getOrder())
        //    ->save();
        
        
        
        
        $magentoCreditmemo->setState(Mage_Sales_Model_Order_Creditmemo::STATE_REFUNDED);
        //$magentoCreditmemo->setTotalQty($totalQuantity);
        
        return $magentoCreditmemo;
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
