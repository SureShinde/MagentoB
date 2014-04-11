<?php
class RocketWeb_Netsuite_Helper_Mapper_Inventory extends RocketWeb_Netsuite_Helper_Mapper {
    public function getInventoryAdjustmentNameForNewProduct($magentoProduct) {
        return 'NEW_'.$magentoProduct->getSku();
    }

    public function isAdjustmentSentFromMagento(InventoryAdjustment $adjustment) {
        if(strpos($adjustment->externalId,'NEW_')!==FALSE) {
            return true;
        }
        else {
            return false;
        }
    }
}