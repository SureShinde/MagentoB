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
    public function getMagentoFormat(ItemFulfillment $netsuiteShipment, $queueData) {
        $lastModifiedDate = Mage::helper('rocketweb_netsuite')->convertNetsuiteDateToSqlFormat($netsuiteShipment->lastModifiedDate);
        $netsuiteShipmentInternalId = $netsuiteShipment->internalId;
        $magentoShipment = $this->getMagentoShipment($netsuiteShipment);
        $magentoOrder = null;
        
        if (!$magentoShipment) {
            $netsuiteOrderId = $netsuiteShipment->createdFrom->internalId;
            $magentoOrders = Mage::getModel('sales/order')->getCollection()->addFieldToFilter('netsuite_internal_id', $netsuiteOrderId);
            $magentoOrder = $magentoOrders->getFirstItem(); // @var Mage_Sales_Model_Order $magentoOrder

            if (!is_object($magentoOrder) || !$magentoOrder->getId()) {
                throw new Exception("Order with netsuite internal id {$netsuiteShipment->createdFrom->internalId} not found in Magento!");
            }
            
            $netsuiteCustomer = Mage::helper('rocketweb_netsuite/mapper_customer')->getByInternalId($netsuiteShipment->entity->internalId);
            $magentoShipment = $this->createMagentoShipment($netsuiteShipment, $magentoOrder, $queueData);
            
            if (is_array($magentoShipment)) {
                return $magentoShipment;
            }
            
            if ($magentoOrder->getPayment()->getMethodInstance()->getCode() == 'cod') {
                if ($magentoOrder->getStatus() == 'processing_cod') {
                    $magentoOrder->setStatus('shipping_cod');
                    $magentoOrder->addStatusHistoryComment('Net Suite status change', 'shipping_cod');
                    $magentoOrder->getStatusHistoryCollection()->save();
                    $magentoOrder->save();
                }
            }
        }
        
        /**
         * save tracking number
         */
        $trackingNumbers = Mage::helper('rocketweb_netsuite/mapper_trackingnumber')->getNormalizedTrackingNumberData($netsuiteShipment);

        if (count($trackingNumbers)) {
            foreach ($trackingNumbers as $trackingNumberData) {
                $magentoTrackingNumber = Mage::helper('rocketweb_netsuite/mapper_trackingnumber')->getMagentoFormat($trackingNumberData, $netsuiteShipment->shipMethod);
                $magentoShipment->addTrack($magentoTrackingNumber);
            }
            
            if (!$magentoOrder) {
                $magentoOrder = $magentoShipment->getOrder();
            }
            if ($magentoOrder->getPayment()->getMethodInstance()->getCode() == 'cod') {
            	$magentoOrderStatus = 'shipping_cod';
                $magentoOrder->setStatus($magentoOrderStatus);
                $magentoOrder->save();
            }
        }
        else {
            /**
             * shipping status comment
             * picked - packed - shipped
             */
            if (in_array($netsuiteShipment->shipStatus, array ('_picked', '_packed', '_shipped'))) {
                if (!$magentoOrder) {
                    $magentoOrder = $magentoShipment->getOrder();
                }
                
                if ($magentoOrder->getPayment()->getMethodInstance()->getCode() == 'cod') {
                    $magentoOrderStatus = 'shipping_cod';
                    $magentoOrder->setStatus($magentoOrderStatus);
                }
                else {
                    $magentoOrderStatus = $magentoOrder->getStatus();
                }
                
                $shipStatus = str_replace('_', '', $netsuiteShipment->shipStatus);
                $magentoOrder->addStatusHistoryComment("The order was {$shipStatus} at {$lastModifiedDate}", $magentoOrderStatus);
                $magentoOrder->getStatusHistoryCollection()->save();
                $magentoOrder->save();
            }
        }
        
        $magentoShipment->sendEmail(true);
        $magentoShipment->setEmailSent(true);
        $magentoShipment->save();
        
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
    
    protected function createMagentoShipment(ItemFulfillment $netsuiteShipment, Mage_Sales_Model_Order $magentoOrder, $queueData) {
        // array of netsuite product
        $netProducts = array();
        // array of magento product
        $itemParent = array ();
        // array of product to be saved
        $prodToSaved = array ();
        // array of product to be shiped
        $prodToShiped = array ();
        
        /**
         * collect data from netsuite
         */
        foreach ($netsuiteShipment->itemList->item as $netsuiteShipmentItem) {
            if (isset ($netProducts[$netsuiteShipmentItem->description]['quantity'])) {
                $netProducts[$netsuiteShipmentItem->description]['quantity'] += $netsuiteShipmentItem->quantity;
            }
            else {
                $netProducts[$netsuiteShipmentItem->description]['quantity'] = $netsuiteShipmentItem->quantity;
            }
	
            $netProducts[$netsuiteShipmentItem->description]['sku'] = $netsuiteShipmentItem->description;
            $netProducts[$netsuiteShipmentItem->description]['internalId'] = $netsuiteShipmentItem->item->internalId;
        }
        
        /**
         * collect data from magento order
         */
        foreach ($magentoOrder->getAllItems() as $magentoOrderItem) {
            if (!$magentoOrderItem->getParentItemId()) {
                $itemParent[$magentoOrderItem->getSku()] = array (
                    'sku' => $magentoOrderItem->getSku(),
                    'qty' => $magentoOrderItem->getQtyOrdered() - $magentoOrderItem->getQtyShipped(),
                    'item_id' => $magentoOrderItem->getId(),
                    'product_id' => $magentoOrderItem->getProductId(),
                    'netsuite_internal_id' => $magentoOrderItem->getNetsuiteInternalId()
                );
            }
        }
        
        /**
         * compare data
         */
        foreach ($netProducts as $key => $netProduct) {
            if (array_key_exists($key, $itemParent)) {
                $prodToShiped[$itemParent[$key]['item_id']] = $netProduct['quantity'];

                if ($netProduct['quantity'] > $itemParent[$key]['qty']) {
                    $qty = $netProduct['quantity'] - $itemParent[$key]['qty'];
                    $this->saveBundleConfigurable($magentoOrder->getIncrementId(), $netProduct['sku'], $qty, $netsuiteShipment->lastModifiedDate);
                }
            }
            else {
                $qty = $netProduct['quantity'];
                $this->saveBundleConfigurable($magentoOrder->getIncrementId(), $netProduct['sku'], $qty, $netsuiteShipment->lastModifiedDate);
            }
        }
        
        /**
         * Check shipment create availability
         */
        if (!$magentoOrder->canShip()) {
            return array (
                'status' => false,
                'message' => "{$magentoOrder->getId()}: Cannot do shipment for this order!",
                'code' => 1
            );
        }
        
        /**
         * check item to ship
         */
        if (count($prodToShiped) == 0) {
            return array (
                'status' => false,
                'message' => "{$magentoOrder->getId()}: Cannot create shipment, because there is no order item!",
                'code' => 2
            );
        }
        
        $magentoShipment = $magentoOrder->prepareShipment($prodToShiped);
        
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
                throw new Exception("{$magentoOrder->getIncrementId()}: {$e->getMessage()}");
            }
        }
        
        return null;
    }

    protected function saveBundleConfigurable($magentoOrderIncrementId, $magentoOrderItemSku, $qty, $lastModifiedDate) {
        $resource = Mage::getSingleton('core/resource');
        $writeConnection = $resource->getConnection('core_write');
        $table = 'netsuite_shipment_bundle';
        $query = sprintf(
            "INSERT INTO %s(order_increment_id, sku, qty_shipped, import_date)
            VALUES(%s, '%s', %d, '%s')",
            $table,
            $magentoOrderIncrementId,
            $magentoOrderItemSku,
            $qty,
            Mage::helper('rocketweb_netsuite')->convertNetsuiteDateToSqlFormat($lastModifiedDate)
        );
        $writeConnection->query($query);
    }
}
