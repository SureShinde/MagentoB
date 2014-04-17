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
class RocketWeb_Netsuite_Model_Process_Delete_Inventory extends RocketWeb_Netsuite_Model_Process_Delete_Abstract {
    public function processDeleteOperation(DeletedRecord $record) {
        if(!$record->record->internalId) {
            return;
        }
        $adjustmentInventory = Mage::getModel('rocketweb_netsuite/adjustmentinventory')->loadByNetsuiteId($record->record->internalId);
        if($adjustmentInventory->getId()) {

            $inventoryAdjustmentDate = new DateTime($adjustmentInventory->getLastUpdateAt());
            $deleteOperationDate = new DateTime($record->deletedDate);

            if($deleteOperationDate->getTimestamp()<=$inventoryAdjustmentDate->getTimestamp()) {
                return;
            }

            $quantities = $adjustmentInventory->getQuantities();
            foreach($quantities as $sku=>$qty) {
                $product = Mage::getModel('catalog/product')->loadByAttribute('sku',$sku);
                if($product && $product->getId()) {
                    $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
                    if($stockItem->getManageStock()) {
                        $stockItem->setQty($stockItem->getQty()-$qty);
                        if($stockItem->verifyStock()) {
                            $stockItem->setIsInStock(true);
                        }
                        else {
                            $stockItem->setIsInStock(false);
                        }
                        $stockItem->save();
                        $product->setData('last_netsuite_stock_update', Mage::helper('rocketweb_netsuite')->convertNetsuiteDateToSqlFormat($record->deletedDate))->getResource()->saveAttribute($product, 'last_netsuite_stock_update');
                    }
                }
            }
            $adjustmentInventory->setLastUpdateAt(Mage::helper('rocketweb_netsuite')->convertNetsuiteDateToSqlFormat($record->deletedDate));
            $adjustmentInventory->save();
        }
    }
}