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

class RocketWeb_Netsuite_Helper_Mapper_Invoice extends RocketWeb_Netsuite_Helper_Mapper {

    public function cleanupNetsuiteCashSale(CashSale $cashSale,Mage_Sales_Model_Order_Invoice $magentoInvoice) {

        $cashSale->createdFrom = new RecordRef();
        $cashSale->createdFrom->internalId = $magentoInvoice->getOrder()->getNetsuiteInternalId();
        $cashSale->createdFrom->type = RecordType::salesOrder;

        //adjust invoice elements (an invoice may not have all elements or the same quantity as the order
        foreach($cashSale->itemList->item as &$netsuiteItem) {
            $found = false;
            foreach($magentoInvoice->getAllItems() as $magentoItem) {
                if($magentoItem->getSku() == $netsuiteItem->item->name) {
                    $netsuiteItem->quantity = $magentoItem->getQty();
                    $netsuiteItem->amount = $magentoItem->getRowTotal();
                    $found = true;
                    break;
                }
            }
            if(!$found) {
                unset($netsuiteItem);
            }
        }

        return $cashSale;
    }

    public function cleanupNetsuiteInvoice(Invoice $invoice, Mage_Sales_Model_Order_Invoice $magentoInvoice) {
        $invoice->createdFrom = new RecordRef();
        $invoice->createdFrom->internalId = $magentoInvoice->getOrder()->getNetsuiteInternalId();
        $invoice->createdFrom->type = RecordType::salesOrder;

        //adjust invoice elements (an invoice may not have all elements or the same quantity as the order
        foreach ($invoice->itemList->item as &$netsuiteItem) {
            $found = false;
            
            foreach ($magentoInvoice->getAllItems() as $magentoItem) {
                if ($magentoItem->getSku() == $netsuiteItem->item->name) {
                    $netsuiteItem->quantity = $magentoItem->getQty();
                    $netsuiteItem->amount = $magentoItem->getRowTotal();
                    $found = true;
                    break;
                }
            }
            
            if (!$found) {
                unset ($netsuiteItem);
            }
        }
        
        //set tranId
        if ($magentoInvoice->getIncrementId()) {
            $invoice->tranId = $magentoInvoice->getIncrementId();
        }
        
        //set magento invoice date
        if ($magentoInvoice->getCreatedAt()) {
            $customMagentoInvoiceDate = new StringCustomFieldRef();
            $customMagentoInvoiceDate->internalId = 'custbody_magentoinvoicedate';
            $customMagentoInvoiceDate->value = date("d/m/Y", strtotime($magentoInvoice->getCreatedAt()));
            $invoice->customFieldList->customField[] = $customMagentoInvoiceDate;
        }
        
        //$netsuiteOrderItem->customFieldList->customField = $customFields;

        return $invoice;
    }

    public function getMagentoFormatFromCashSale(CashSale $cashSale) {
        $magentoInvoice = Mage::getModel('sales/order_invoice');
        $netsuiteOrderId = $cashSale->createdFrom->internalId;
        $magentoOrders = Mage::getModel('sales/order')->getCollection()->addFieldToFilter('netsuite_internal_id', $netsuiteOrderId);
        $magentoOrder = $magentoOrders->getFirstItem();
        
        if (!is_object($magentoOrder) || !$magentoOrder->getId()) {
            throw new Exception("Order with netsuite internal id {$cashSale->createdFrom->internalId} not found in Magento!");
        }

        $netsuiteCustomer = Mage::helper('rocketweb_netsuite/mapper_customer')->getByInternalId($cashSale->entity->internalId);
        $magentoInvoice->setOrder($magentoOrder)->setStoreId($magentoOrder->getStoreId())->setCustomerId($magentoOrder->getCustomerId());

        Mage::helper('core')->copyFieldset('sales_convert_order', 'to_invoice', $magentoOrder, $magentoInvoice);

        //if($cashSale->transactionBillAddress) {
        //    $magentoBillingAddress = Mage::helper('rocketweb_netsuite/mapper_address')->getBillingAddressMagentoFormatFromNetsuiteAddress($cashSale->transactionBillAddress,
        //        $netsuiteCustomer,
        //        $magentoOrder);
        //    $magentoBillingAddress->setId($magentoOrder->getBillingAddressId());
        //    $magentoBillingAddress->save();
        //}
        
        $magentoInvoice->setBillingAddressId($magentoOrder->getBillingAddressId())->setShippingAddressId($magentoOrder->getShippingAddressId());
        $itemMap = array ();
        
        foreach ($cashSale->itemList->item as $netsuiteItem) {
            foreach ($magentoOrder->getAllItems() as $magentoOrderItem) {
                if ($netsuiteItem->item->internalId == Mage::getModel('catalog/product')->load($magentoOrderItem->getProductId())->getNetsuiteInternalId()) {
                    $item = array ();
                    $item['netsuite_object'] = $netsuiteItem;
                    $item['magento_orderitem_object'] = $magentoOrderItem;
                    $itemMap[] = $item;
                }
            }
        }
        
        $totalQuantity = 0;
        
        if (is_array($itemMap)) {
            foreach ($itemMap as $item) {
                $magentoInvoiceItem = Mage::getModel('sales/order_invoice_item');
                Mage::helper('core')->copyFieldset('sales_convert_order_item', 'to_invoice_item', $item['magento_orderitem_object'], $magentoInvoiceItem);
                $magentoInvoiceItem->setOrderItem($item['magento_orderitem_object']);
                $magentoInvoiceItem->setProductId(Mage::getModel('catalog/product')->load($item['magento_orderitem_object']->getProductId())->getId());
                //intentionally using setData instead of ->setQty as at this point we dont care about out of stock exceptions
                $magentoInvoiceItem->setData('qty', $item['netsuite_object']->quantity);
                $magentoInvoice->addItem($magentoInvoiceItem);
                $totalQuantity += $item['netsuite_object']->quantity;
            }
        }
        
        $magentoInvoice->setState(Mage_Sales_Model_Order_Invoice::STATE_PAID);
        $magentoInvoice->setTotalQty($totalQuantity);
        
        //$_totalPaid = $cashSale->total;
        //$_totalDue = $magentoInvoice->getOrder()->getGrandTotal() - $_totalPaid;
        //$magentoInvoice->getOrder()->setTotalPaid($_totalPaid)->setBaseTotalPaid($_totalPaid);
        //$magentoInvoice->getOrder()->setTotalDue($_totalDue)->setBaseTotalDue($_totalDue);
        
        return $magentoInvoice;
    }

    public function getMagentoFormatFromInvoice(Invoice $invoice) {
        $magentoInvoice = Mage::getModel('sales/order_invoice');

        $netsuiteOrderId = $invoice->createdFrom->internalId;
        $magentoOrders = Mage::getModel('sales/order')->getCollection()->addFieldToFilter('netsuite_internal_id',$netsuiteOrderId);
        /** @var Mage_Sales_Model_Order $magentoOrder */
        $magentoOrder = $magentoOrders->getFirstItem();
        if(!is_object($magentoOrder) || !$magentoOrder->getId()) {
            throw new Exception("Order with netsuite internal id {$invoice->createdFrom->internalId} not found in Magento!");
        }

        $netsuiteCustomer = Mage::helper('rocketweb_netsuite/mapper_customer')->getByInternalId($invoice->entity->internalId);
        $magentoInvoice->setOrder($magentoOrder)
            ->setStoreId($magentoOrder->getStoreId())
            ->setCustomerId($magentoOrder->getCustomerId());

        Mage::helper('core')->copyFieldset('sales_convert_order', 'to_invoice', $magentoOrder, $magentoInvoice);

        if($invoice->transactionBillAddress) {
            $magentoBillingAddress = Mage::helper('rocketweb_netsuite/mapper_address')->getBillingAddressMagentoFormatFromNetsuiteAddress($invoice->transactionBillAddress,
                $netsuiteCustomer,
                $magentoOrder);
            $magentoBillingAddress->setId($magentoOrder->getBillingAddressId());
            $magentoBillingAddress->save();
        }

        $magentoInvoice->setBillingAddressId($magentoOrder->getBillingAddressId())->setShippingAddressId($magentoOrder->getShippingAddressId());

        $itemMap = array();
        foreach($invoice->itemList->item as $netsuiteItem) {
            foreach($magentoOrder->getAllItems() as $magentoOrderItem) {
                if($netsuiteItem->item->internalId == Mage::getModel('catalog/product')->load($magentoOrderItem->getProductId())->getNetsuiteInternalId()) {
                    $item = array();
                    $item['netsuite_object'] = $netsuiteItem;
                    $item['magento_orderitem_object'] = $magentoOrderItem;
                    $itemMap[]=$item;
                }
            }
        }
        $totalQuantity = 0;
        if(is_array($itemMap)) {
            foreach($itemMap as $item) {
                $magentoInvoiceItem = Mage::getModel('sales/order_invoice_item');
                Mage::helper('core')->copyFieldset('sales_convert_order_item', 'to_invoice_item', $item['magento_orderitem_object'], $magentoInvoiceItem);
                $magentoInvoiceItem->setOrderItem($item['magento_orderitem_object']);
                $magentoInvoiceItem->setProductId(Mage::getModel('catalog/product')->load($item['magento_orderitem_object']->getProductId())->getId());

                //intentionally using setData instead of ->setQty as at this point we dont care about out of stock exceptions
                $magentoInvoiceItem->setData('qty',$item['netsuite_object']->quantity);
                $magentoInvoice->addItem($magentoInvoiceItem);
                $totalQuantity+=$item['netsuite_object']->quantity;
            }
        }
        $magentoInvoice->setState(Mage_Sales_Model_Order_Invoice::STATE_PAID);
        $magentoInvoice->setTotalQty($totalQuantity);
        return $magentoInvoice;
    }
}