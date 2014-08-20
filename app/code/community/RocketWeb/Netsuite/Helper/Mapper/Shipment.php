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
        $magentoItems = array ();
        // array of product to be saved
        $prodToSaved = array ();
        // array of product to be shiped
        $prodToShiped = array ();
        
        /**
         * collect data from netsuite
         */
        foreach ($netsuiteShipment->itemList->item as $netsuiteShipmentItem) {
            $netsuiteMagentoItemId = $this->getMagentoObjectFromNetsuite($netsuiteShipmentItem, 'custcol_magentoitemid');
            
            if (isset ($netProducts[$netsuiteMagentoItemId]['quantity'])) {
                $netProducts[$netsuiteMagentoItemId]['quantity'] += $netsuiteShipmentItem->quantity;
            }
            else {
                $netProducts[$netsuiteMagentoItemId]['quantity'] = $netsuiteShipmentItem->quantity;
            }
	
            $netProducts[$netsuiteMagentoItemId]['sku'] = $netsuiteShipmentItem->description;
            $netProducts[$netsuiteMagentoItemId]['internalId'] = $netsuiteShipmentItem->item->internalId;
            $netProducts[$netsuiteMagentoItemId]['itemId'] = $netsuiteMagentoItemId;
            $netProducts[$netsuiteMagentoItemId]['parentId'] = $this->getMagentoObjectFromNetsuite($netsuiteShipmentItem, 'custcol_parentid');
            $netProducts[$netsuiteMagentoItemId]['parentType'] = $this->getMagentoObjectFromNetsuite($netsuiteShipmentItem, 'custcol_parentname');
        }
        
        /**
         * collect data from magento order
         */
        foreach ($magentoOrder->getAllItems() as $magentoOrderItem) {
            $magentoItems[$magentoOrderItem->getId()] = array (
                'sku' => $magentoOrderItem->getSku(),
                'qty' => $magentoOrderItem->getQtyOrdered() - $magentoOrderItem->getQtyShipped(),
                'item_id' => $magentoOrderItem->getId(),
                'parent_id' => $magentoOrderItem->getParentItemId(),
                'type' => $magentoOrderItem->getProductType(),
                'product_id' => $magentoOrderItem->getProductId(),
                'netsuite_internal_id' => $magentoOrderItem->getNetsuiteInternalId()
            );
        }
        
        /**
         * compare data
         */
        foreach ($netProducts as $key => $netProduct) {
            if (array_key_exists($key, $magentoItems)) {
                if ($netProduct['quantity'] > $magentoItems[$key]['qty']) {
                    throw new Exception("{$magentoOrder->getId()}: Error quantity bigger than it should be!");
                }
                else {
                    $prodToShiped[$key] = $netProduct['quantity'];
                    
                    if (isset ($magentoItems[$key]['parent_id']) && ($magentoItems[$key]['parent_id'] !== 0)) {
                        $parentKey = $magentoItems[$key]['parent_id'];
                        
                        if ($magentoItems[$parentKey]['type'] == 'configurable') {
                            $prodToShiped[$parentKey] = $netProduct['quantity'];
                        }
                        else if ($magentoItems[$parentKey]['type'] == 'bundle') {
                            $prodToShiped[$parentKey] = (int) ($magentoItems[$parentKey]['quantity'] * ($magentoItems[$key]['quantity'] / $netProduct['quantity']));
                        }
                    }
                }
            }
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
        
        /**
         * Check shipment create availability
         */
        if (!$magentoOrder->canShip()) {
            throw new Exception("{$magentoOrder->getId()}: Cannot do shipment for this order!");
        }
        
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
    
    protected function getMagentoObjectFromNetsuite($netsuiteShipmentItem, $internalId) {
        foreach ($netsuiteShipmentItem->customFieldList->customField as $customField) {
            if ($customField->internalId == $internalId) {
                return $customField->value;
            }
        }
        
        return '';
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
