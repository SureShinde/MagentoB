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

class RocketWeb_Netsuite_Model_Process_Import_Inventory extends RocketWeb_Netsuite_Model_Process_Import_Abstract {

    public function getPermissionName() {
        return RocketWeb_Netsuite_Helper_Permissions::GET_STOCK_UPDATES;
    }

    public function getRecordType() {
        return RecordType::inventoryAdjustment;
    }

    public function getMessageType() {
        return RocketWeb_Netsuite_Model_Queue_Message::INVENTORY_UPDATED;
    }

    public function getDeleteMessageType() {
        return RocketWeb_Netsuite_Model_Queue_Message::INVENTORY_DELETED;
    }

    public function process(Record $inventoryAdjustment, $queueData = null) {
        /** @var InventoryAdjustment $inventoryAdjustment */
        $adjustmentInventory = Mage::getModel('rocketweb_netsuite/adjustmentinventory')->loadByNetsuiteId($inventoryAdjustment->internalId);

        foreach($inventoryAdjustment->inventoryList->inventory as $inventoryLine) {
            $product = Mage::getModel('catalog/product')->loadByAttribute('sku',$inventoryLine->item->name);
            if($product && $product->getId()) {

                $inventoryAdjustmentDate = new DateTime($inventoryAdjustment->lastModifiedDate);
                if(is_null($product->getLastNetsuiteStockUpdate())) {
                    $productLastInventoryUpdateDate = null;
                }
                else {
                    $productLastInventoryUpdateDate = new DateTime($product->getLastNetsuiteStockUpdate());
                }

                if(is_null($productLastInventoryUpdateDate) || $inventoryAdjustmentDate->getTimestamp() > $productLastInventoryUpdateDate->getTimestamp()) {
                    $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);

                    if($stockItem->getManageStock()) {
                        if($adjustmentInventory->getInternalNetsuiteId()) {
                            $qty = $stockItem->getQty()+$inventoryLine->adjustQtyBy-$adjustmentInventory->getQtyForSku($inventoryLine->item->name);
                        }
                        else {
                            $qty = $stockItem->getQty()+$inventoryLine->adjustQtyBy;
                        }
                        $stockItem->setQty($qty);
                        if($stockItem->verifyStock()) {
                            $stockItem->setIsInStock(true);
                        }
                        else {
                            $stockItem->setIsInStock(false);
                        }
                        $stockItem->save();
                        $product->setData('last_netsuite_stock_update', Mage::helper('rocketweb_netsuite')->convertNetsuiteDateToSqlFormat($inventoryAdjustment->lastModifiedDate))->getResource()->saveAttribute($product, 'last_netsuite_stock_update');
                    }

                }
            }
        }

        $adjustmentInventory->setInternalNetsuiteId($inventoryAdjustment->internalId);
        $adjustmentInventory->setLastUpdateAt(Mage::helper('rocketweb_netsuite')->convertNetsuiteDateToSqlFormat($inventoryAdjustment->lastModifiedDate));
        $adjustmentInventory->setQuantitiesFromInventoryList($inventoryAdjustment->inventoryList);
        $adjustmentInventory->save();

    }

    public function isMagentoImportable(Record $inventoryAdjustment) {
        if($inventoryAdjustment->adjLocation) {
            return $this->stockLocationIsConsidered($inventoryAdjustment->adjLocation);
        }
        else {
            return true;
        }
    }

    public function isAlreadyImported(Record $record) {
       //inventory adjustments can contain both products that are in Magento and products that are not.
       //Always return true here, we will handle non-importables when processing
       return false;
    }

    public function isActive() {
        return true;
    }

    /*
     * Magento will only grab updates from a pre-defined locations list. This method test if a certain location
     * is in that list.
     */
    public function stockLocationIsConsidered(RecordRef $location) {
        $consideredLocations = explode(',',Mage::getStoreConfig('rocketweb_netsuite/stock/grab_stock_from'));
        if(in_array($location->internalId,$consideredLocations)) {
            return true;
        }
        else {
            return false;
        }
    }
}