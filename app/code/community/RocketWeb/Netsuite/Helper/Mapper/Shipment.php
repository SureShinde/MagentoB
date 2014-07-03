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

class RocketWeb_Netsuite_Helper_Mapper_Shipment extends RocketWeb_Netsuite_Helper_Mapper {
    public function getMagentoFormatOld(ItemFulfillment $netsuiteShipment) {
        $magentoShipment = Mage::getModel('sales/order_shipment');
        $netsuiteOrderId = $netsuiteShipment->createdFrom->internalId;
        $magentoOrders = Mage::getModel('sales/order')->getCollection()->addFieldToFilter('netsuite_internal_id', $netsuiteOrderId);
        $magentoOrder = $magentoOrders->getFirstItem(); // @var Mage_Sales_Model_Order $magentoOrder

        if (!is_object($magentoOrder) || !$magentoOrder->getId()) {
            throw new Exception("Order with netsuite internal id {$netsuiteShipment->createdFrom->internalId} not found in Magento!");
        }

        $netsuiteCustomer = Mage::helper('rocketweb_netsuite/mapper_customer')->getByInternalId($netsuiteShipment->entity->internalId);
        $magentoShipment->setStoreId($magentoOrder->getStoreId());
        $magentoShipment->setCustomerId($magentoOrder->getCustomerId()); //we assume customer is not changed for shipping
        $magentoShipment->setBillingAddressId($magentoOrder->getBillingAddressId()); //billing address is not part of a netsuite fulfillment, use the one in Magento
        $magentoShippingAddress = Mage::helper('rocketweb_netsuite/mapper_address')->getShippingAddressMagentoFormatFromNetsuiteAddress($netsuiteShipment->transactionShipAddress, $netsuiteCustomer, $magentoOrder);
        $magentoShippingAddress->setId($magentoOrder->getShippingAddressId());
        $magentoShippingAddress->save();

        Mage::helper('core')->copyFieldset('sales_convert_order', 'to_shipment', $magentoOrder, $magentoShipment);
        $magentoShipment->setShippingAddressId($magentoShippingAddress->getId());
        $magentoShipment->setOrderId($magentoOrder->getId());
        $magentoShipment->setCustomerId($magentoOrder->getCustomerId());
        $shipmentMap = array ();

        foreach ($netsuiteShipment->itemList->item as $netsuiteShipmentItem) {
            foreach ($magentoOrder->getAllItems() as $magentoOrderItem) {
                if ($netsuiteShipmentItem->item->name == $magentoOrderItem->getName()) {
                    $shipmentMapItem = array ();
                    $shipmentMapItem['netsuite_object'] = $netsuiteShipmentItem;
                    $shipmentMapItem['magento_orderitem_object'] = $magentoOrderItem;
                    $shipmentMap[] = $shipmentMapItem;
                }
            }
        }

        $totalQuantity = 0;

        if (is_array($shipmentMap)) {
            foreach ($shipmentMap as $shipmentMapItem) {
                $magentoShipmentItem = Mage::getModel('sales/order_shipment_item');
                Mage::helper('core')->copyFieldset('sales_convert_order_item', 'to_shipment_item', $shipmentMapItem['magento_orderitem_object'], $magentoShipmentItem);
                $magentoShipmentItem->setOrderItem($shipmentMapItem['magento_orderitem_object']);
                $magentoShipmentItem->setProductId(Mage::getModel('catalog/product')->load($shipmentMapItem['magento_orderitem_object']->getProductId())->getId());
                $magentoShipmentItem->setQty($shipmentMapItem['netsuite_object']->quantity);
                $magentoShipmentItem->getOrderItem()->setQtyShipped($shipmentMapItem['netsuite_object']->quantity);
                $magentoShipment->addItem($magentoShipmentItem);
                $totalQuantity += $shipmentMapItem['netsuite_object']->quantity;
            }
        }

        $magentoShipment->setTotalQty($totalQuantity);
        $trackingNumbers = Mage::helper('rocketweb_netsuite/mapper_trackingnumber')->getNormalizedTrackingNumberData($netsuiteShipment);

        if (count($trackingNumbers)) {
            foreach ($trackingNumbers as $trackingNumberData) {
                $magentoTrackingNumber = Mage::helper('rocketweb_netsuite/mapper_trackingnumber')->getMagentoFormat($trackingNumberData, $netsuiteShipment->shipMethod);
                $magentoShipment->addTrack($magentoTrackingNumber);
            }
        }
        
        return $magentoShipment;
    }
    
    public function getMagentoFormat(ItemFulfillment $netsuiteShipment) {
        $netsuiteShipmentInternalId = $netsuiteShipment->internalId;
        $magentoShipment = $this->getMagentoShipment($netsuiteShipment);
        
        if (!$magentoShipment) {
            $netsuiteOrderId = $netsuiteShipment->createdFrom->internalId;
            $magentoOrders = Mage::getModel('sales/order')->getCollection()->addFieldToFilter('netsuite_internal_id', $netsuiteOrderId);
            $magentoOrder = $magentoOrders->getFirstItem(); // @var Mage_Sales_Model_Order $magentoOrder

            if (!is_object($magentoOrder) || !$magentoOrder->getId()) {
                throw new Exception("Order with netsuite internal id {$netsuiteShipment->createdFrom->internalId} not found in Magento!");
            }

            $netsuiteCustomer = Mage::helper('rocketweb_netsuite/mapper_customer')->getByInternalId($netsuiteShipment->entity->internalId);
            $magentoShipment = $this->createMagentoShipment($netsuiteShipment, $magentoOrder);
        }
        
        $trackingNumbers = Mage::helper('rocketweb_netsuite/mapper_trackingnumber')->getNormalizedTrackingNumberData($netsuiteShipment);

        if (count($trackingNumbers)) {
            foreach ($trackingNumbers as $trackingNumberData) {
                $magentoTrackingNumber = Mage::helper('rocketweb_netsuite/mapper_trackingnumber')->getMagentoFormat($trackingNumberData, $netsuiteShipment->shipMethod);
                $magentoShipment->addTrack($magentoTrackingNumber);
            }
        }
        
        $magentoShipment->setEmailSent(true);
        $magentoShipment->sendEmail(true);
        
        return $magentoShipment;
    }
    
    protected function getMagentoShipment(ItemFulfillment $netsuiteShipment) {
        $shipmentCollection = Mage::getModel('sales/order_shipment')->getCollection();
        $shipmentCollection->addFieldToFilter('netsuite_internal_id', $netsuiteShipment->internalId);
        
        if ($shipmentCollection->count()) {
            return $shipmentCollection->getFirstItem();
        }
        
        return null;
    }
    
    protected function createMagentoShipment(ItemFulfillment $netsuiteShipment, Mage_Sales_Model_Order $magentoOrder) {
        $shipmentMap = array ();
        $itemQty = array ();
        
        foreach ($netsuiteShipment->itemList->item as $netsuiteShipmentItem) {
            foreach ($magentoOrder->getAllItems() as $magentoOrderItem) {
                if ($netsuiteShipmentItem->item->internalId == Mage::getModel('catalog/product')->load($magentoOrderItem->getProductId())->getNetsuiteInternalId()) {
                    $shipmentMapItem = array ();
                    $shipmentMapItem['netsuite_object'] = $netsuiteShipmentItem;
                    $shipmentMapItem['magento_orderitem_object'] = $magentoOrderItem;
                    $shipmentMap[] = $shipmentMapItem;
                }
            }
        }
        
        if (is_array($shipmentMap)) {
            foreach ($shipmentMap as $shipmentMapItem) {
                $itemQty[$shipmentMapItem['magento_orderitem_object']->getId()] = $shipmentMapItem['netsuite_object']->quantity;
            }
        }
        
        /**
         * Check shipment create availability
         */
        if (!$magentoOrder->canShip()) {
             throw new Exception("{$magentoOrder->getId()}: Cannot do shipment for this order!");
        }
            
        $magentoShipment = $magentoOrder->prepareShipment($itemQty);
        
        if ($magentoShipment) {
            $magentoShipment->register();
            $magentoShipment->addComment("Create Shipment from Netsuite #{$netsuiteShipment->internalId}");
            $magentoShipment->getOrder()->setIsInProcess(true);
            
            try {
                $transactionSave = Mage::getModel('core/resource_transaction')
                    ->addObject($magentoShipment)
                    ->addObject($magentoShipment->getOrder())
                    ->save();
                
                return $magentoShipment;
            }
            catch (Mage_Core_Exception $e) {
                throw new Exception("{$magentoOrder->getId()}: {$e->getMessage()}");
            }
        }
        
        return null;
    }
}
