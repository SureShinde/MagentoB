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
            $magentoShipment = $this->createMagentoShipment($netsuiteShipment, $magentoOrder);
            
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
//                 $magentoShipment->sendEmail(true, ''); 
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
                //$magentoOrder->setStatus($magentoOrderStatus);
                $magentoOrder->addStatusHistoryComment("The order was {$shipStatus} at {$lastModifiedDate}", $magentoOrderStatus);
                $magentoOrder->getStatusHistoryCollection()->save();
                $magentoOrder->save();
            }
        }
        
//         if (!$magentoShipment->getEmailSent()) { 
            $magentoShipment->sendEmail(true);
            $magentoShipment->setEmailSent(true);
//         }
        
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
    
    protected function createMagentoShipment(ItemFulfillment $netsuiteShipment, Mage_Sales_Model_Order $magentoOrder) {
		$shipmentMap = array ();
        $itemParents = array ();
        $itemShip = array ();
		
        foreach ($netsuiteShipment->itemList->item as $netsuiteShipmentItem) {
			foreach ($magentoOrder->getAllItems() as $magentoOrderItem) {
                if ($netsuiteShipmentItem->item->internalId == Mage::getModel('catalog/product')->load($magentoOrderItem->getProductId())->getNetsuiteInternalId()) {
                    $shipmentMapItem = array ();
                    $shipmentMapItem['netsuite_object'] = $netsuiteShipmentItem;
                    $shipmentMapItem['magento_orderitem_object'] = $magentoOrderItem;
                    $shipmentMap[] = $shipmentMapItem;

                    if (!$magentoOrderItem->getParentItemId()) {
                        $itemParent[$magentoOrderItem->getId()] = array (
							'sku' => $magentoOrderItem->getSku(),
                            'qty' => $magentoOrderItem->getQtyOrdered() - $magentoOrderItem->getQtyShipped()
                        );
                    }

					break;
                }
            }
        }

		echo "[{$magentoOrder->getIncrementId()}]: " . json_encode($shipmentMap) . "\n";
		echo "[{$magentoOrder->getIncrementId()}] totalShipmentMap: " . count($shipmentMap) . "\n";
		echo "[{$magentoOrder->getIncrementId()}] totalItemParent: " . count($itemParent) . "\n";
		//exit;
        
        if (is_array($shipmentMap)) {
            foreach ($shipmentMap as $shipmentMapItem) {
                //if ($this->checkItemParent($shipmentMapItem['magento_orderitem_object']->getId(), $itemParent)) {
				if ($itemParent[$shipmentMapItem['magento_orderitem_object']->getId()] && ($shipmentMapItem['netsuite_object']->quantity <= $itemParent[$shipmentMapItem['magento_orderitem_object']->getId()]['qty'])) {
	                $itemShip[$shipmentMapItem['magento_orderitem_object']->getId()] = $shipmentMapItem['netsuite_object']->quantity;
                    $itemParent[$shipmentMapItem['magento_orderitem_object']->getId()]['qty'] = $itemParent[$shipmentMapItem['magento_orderitem_object']->getId()]['qty'] - $shipmentMapItem['netsuite_object']->quantity;
                }
				else {
					$this->saveBundleConfigurable($netsuiteShipment, $shipmentMapItem['netsuite_object'], $shipmentMapItem['magento_orderitem_object']);
				}
            }
        }
        
        /**
         * Check shipment create availability
         */
        if (!$magentoOrder->canShip()) {
             throw new Exception("{$magentoOrder->getId()}: Cannot do shipment for this order!");
        }
        
        if (count($itemShip) == 0) {
            throw new Exception("{$magentoOrder->getId()}: Cannot create shipment, because there is no order item!");
        }
        
        echo $magentoOrder->getIncrementId() . ": itemParent: " . json_encode($itemParent) . "\n";
        echo $magentoOrder->getIncrementId() . ": itemShip: " . json_encode($itemShip) . "\n";
        $magentoShipment = $magentoOrder->prepareShipment($itemShip);		
        
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

	protected function checkItemParent($itemId, $itemParent) {
        foreach ($itemParent as $key => $value) {
            if ($itemId == $key) {
				echo "$sku - $key\n";
                return true;
            }
        }
        
        return false;
    }

    protected function saveBundleConfigurable(ItemFulfillment $netsuiteShipment, $netsuiteShipmentItem, Mage_Sales_Model_Order_Item $magentoOrderItem) {
		$resource = Mage::getSingleton('core/resource');
        $writeConnection = $resource->getConnection('core_write');
        $table = 'netsuite_shipment_bundle';
        $query = sprintf(
            "INSERT INTO %s(order_increment_id, sku, qty_shipped, import_date)
            VALUES(%s, '%s', %d, '%s')",
            $table,
            $magentoOrderItem->getOrder()->getIncrementId(),
            $magentoOrderItem->getSku(),
            $netsuiteShipmentItem->quantity,
            Mage::helper('rocketweb_netsuite')->convertNetsuiteDateToSqlFormat($netsuiteShipment->lastModifiedDate)
        );
		echo $magentoOrderItem->getOrder()->getIncrementId() . ": " . $query . "\n";
        $writeConnection->query($query);
    }
}
