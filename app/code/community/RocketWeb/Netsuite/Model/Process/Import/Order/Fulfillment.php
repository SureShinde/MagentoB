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

class RocketWeb_Netsuite_Model_Process_Import_Order_Fulfillment extends RocketWeb_Netsuite_Model_Process_Import_Abstract {
    public function getPermissionName() {
        return RocketWeb_Netsuite_Helper_Permissions::GET_SHIPMENTS;
    }

    //check if an order with the item fullfilment's createFrom internalId exists in Magento. If not, the record is not for a Magento order
    public function isMagentoImportable(Record $itemFulfillment) {
        /** @var ItemFulfillment $itemFulfillment */
        if (is_null($itemFulfillment->createdFrom)) {
            return false;
        }
        
        $netsuiteOrderId = $itemFulfillment->createdFrom->internalId;
        $magentoOrders = Mage::getModel('sales/order')->getCollection()->addFieldToFilter('netsuite_internal_id', $netsuiteOrderId);
        $magentoOrders->load();
        
        if (!$magentoOrders->getSize()) {
            return false;
        }
        else {
            return true;
        }
    }

    public function isAlreadyImported(Record $record) {
        $shipmentCollection = Mage::getModel('sales/order_shipment')->getCollection();
        $shipmentCollection->addFieldToFilter('netsuite_internal_id', $record->internalId);
        $netsuiteUpdateDatetime = Mage::helper('rocketweb_netsuite')->convertNetsuiteDateToSqlFormat($record->lastModifiedDate);
        $shipmentCollection->addFieldToFilter('last_import_date', array ('gteq' => $netsuiteUpdateDatetime));
        $shipmentCollection->load();
        
        if ($shipmentCollection->count()) {
            return true;
        }
        else {
            return false;
        }
    }

    public function getRecordType() {
        return RecordType::itemFulfillment;
    }

    public function getMessageType() {
        return RocketWeb_Netsuite_Model_Queue_Message::FULFILLMENT_IMPORTED;
    }

    public function getDeleteMessageType() {
        return RocketWeb_Netsuite_Model_Queue_Message::FULFILLMENT_DELETED;
    }

    public function isActive() {
        return true;
    }

    public function process(Record $itemFulfillment) {
        //$this->log("process import orderFullfilment start");
        //$this->log("itemFullfillment: " . json_encode($itemFulfillment));
        
        /** @var ItemFulfillment $itemFulfillment */
        $magentoShipping = Mage::helper('rocketweb_netsuite/mapper_shipment')->getMagentoFormat($itemFulfillment);
        $sendTrackingInformation = $this->getSendTrackingInformation($itemFulfillment);
        $existingShipping = $this->getExistingShipping($itemFulfillment);
        
        if ($existingShipping) {
            foreach ($existingShipping->getAllItems() as $item) {
                $item->delete();
            }
            
            foreach ($existingShipping->getTracksCollection() as $track) {
                $track->delete();
            }
            
            $magentoShipping->setId($existingShipping->getId());
        }

        $magentoShipping->setNetsuiteInternalId($itemFulfillment->internalId);
        $magentoShipping->setLastImportDate(Mage::helper('rocketweb_netsuite')->convertNetsuiteDateToSqlFormat($itemFulfillment->lastModifiedDate));

        if (!$magentoShipping->getCommentsCollection()->count()) {
            //we only want to add an auto-comment when the shipment is created, i.e. when there are no comments
            $magentoShipping->addComment("Imported from Net Suite - fulfillment transaction id #{$itemFulfillment->tranId}", false, false);
        }

        if ($sendTrackingInformation) {
            $magentoShipping->sendEmail(true)->setEmailSent(true);
        }

        Mage::dispatchEvent('netsuite_item_fulfillment_import_save_before', array ('netsuite_shipping' => $itemFulfillment, 'magento_shipping' => $magentoShipping));

        if (!$magentoShipping->getEmailSent()) {
            $magentoShipping->sendEmail(true);
            $magentoShipping->setEmailSent(true);
        }

        $magentoShipping->save();
        $this->updateShippingPrice($magentoShipping, $itemFulfillment);
    }

    protected function updateShippingPrice(Mage_Sales_Model_Order_Shipment $magentoShipment, ItemFulfillment $netsuiteShipment) {
        $magentoOrder = $magentoShipment->getOrder();
        $shippingPriceDifference = $magentoOrder->getShippingAmount() - $netsuiteShipment->shippingCost;
        $shippingPriceFromOtherShipments = 0;
        $magentoShipmentCollection = $magentoOrder->getShipmentsCollection();
        
        foreach ($magentoShipmentCollection as $magentoShipmentCollectionItem) {
            if ($magentoShipmentCollectionItem->getId() != $magentoShipment->getId()) {
                $shippingPriceFromOtherShipments += $magentoShipmentCollectionItem->getShippingAmount();
                $magentoShipmentCollectionItem->getOrderItem()->setQtyShipped($netsuiteItem->quantity)->save();
            }
        }
        
        $shippingCost = $shippingPriceFromOtherShipments + $netsuiteShipment->shippingCost;
        $magentoOrder->setShippingAmount($shippingCost);
        $magentoOrder->setBaseShippingAmount($shippingCost);
        $magentoOrder->setShippingInclTax($shippingCost);
        $magentoOrder->setBaseShippingInclTax($shippingCost);
        $magentoOrder->setGrandTotal($magentoOrder->getGrandTotal() - $shippingPriceDifference);
        $magentoOrder->setBaseGrandTotal($magentoOrder->getBaseGrandTotal() - $shippingPriceDifference);
        $magentoOrder->getResource()->save($magentoOrder);

        $dbConnection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tableName = Mage::getSingleton('core/resource')->getTableName('sales_flat_order_grid');
        $query = "UPDATE $tableName SET grand_total = {$magentoOrder->getGrandTotal()}, base_grand_total = {$magentoOrder->getGrandTotal()} WHERE entity_id = {$magentoOrder->getId()}";
        $dbConnection->query($query);
    }

    protected function getSendTrackingInformation(ItemFulfillment $itemFulfillment) {
        if (!Mage::getStoreConfig('rocketweb_netsuite/shipping_methods/send_tracking_information_on_import')) {
            return false;
        }
        
        $existingShipping = $this->getExistingShipping($itemFulfillment);
        
        if (!is_null($existingShipping)) {
            $existingTrackingCodes = array ();
            
            foreach ($existingShipping->getTracksCollection() as $track) {
                $existingTrackingCodes[] = $track->getTrackNumber();
            }

            $itemFulfillmentData = Mage::helper('rocketweb_netsuite/mapper_trackingnumber')->getNormalizedTrackingNumberData($itemFulfillment);
            
            foreach ($itemFulfillmentData as $itemFulfillmentDataItem) {
                if (!in_array($itemFulfillmentDataItem['number'], $existingTrackingCodes)) {
                    return true;
                }
            }
            
            return false;
        }
        else {
            $itemFulfillmentData = Mage::helper('rocketweb_netsuite/mapper_trackingnumber')->getNormalizedTrackingNumberData($itemFulfillment);
            
            if (count($itemFulfillmentData)) {
                return true;
            }
            else {
                return false;
            }
        }
    }

    protected function getExistingShipping($itemFulfillment) {
        $shipmentCollection = Mage::getModel('sales/order_shipment')->getCollection();
        $shipmentCollection->addFieldToFilter('netsuite_internal_id', $itemFulfillment->internalId);
        
        if ($shipmentCollection->count()) {
            return $shipmentCollection->getFirstItem();
        }
        else {
            return null;
        }
    }
}