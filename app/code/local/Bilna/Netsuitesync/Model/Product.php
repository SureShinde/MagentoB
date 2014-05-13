<?php
/**
 * Description of Bilna_Netsuitesync_Model_Product
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Netsuitesync_Model_Product extends Mage_Core_Model_Abstract {
    public function getProductCollection() {
        $_productCollection = Mage::getModel('catalog/product')->getCollection()->addAttributeToSelect('entity_id');

        return $_productCollection;
    }
    
    public function updateProduct($magentoProduct) {
        $response = false;
        $netsuiteService = Mage::helper('rocketweb_netsuite')->getNetsuiteService();
        
        if ($magentoProduct) {
            //$netsuiteProduct = $this->getNetsuiteFormatProduct($magentoProduct);
            $inventoryItem = new InventoryItem();
            $netsuiteProduct = $this->getNetsuiteFormatProduct($inventoryItem, $magentoProduct);
            
            $request = new UpdateRequest();
            $request->record = $magentoProduct;
            $response = $netsuiteService->update($request);
        }
        
        return $response;
    }
    
    public function getNetsuiteFormatProduct($inventoryItem, Mage_Catalog_Model_Product $magentoProduct) {
        //$netsuiteService = Mage::helper('rocketweb_netsuite')->getNetsuiteService();
        //$inventoryItem = new InventoryItem();
        $inventoryItem->internalId = $netsuiteInternalId;
        $customFieldList = new CustomFieldList();

        //- expected cost
        if ($expectedCost = $magentoProduct->getExpectedCost()) {
            if ($expectedCost == null || $expectedCost == 0) {
                $expectedCost = 0;
            }
        }
        else {
            $expectedCost = 0;
        }

        $customFieldExpectedCost = new StringCustomFieldRef();
        $customFieldExpectedCost->scriptId = 'custitem_expectedcost';
        $customFieldExpectedCost->value = utf8_encode($expectedCost);
        $customFieldList->customField[] = $customFieldExpectedCost;

        //- event cost
        if ($eventCost = $magentoProduct->getEventCost()) {
            if ($eventCost == null || $eventCost == 0) {
                $eventCost = 0;
            }
            else {
                if ($eventStartDate = $magentoProduct->getEventStartDate() && $eventEndDate = $magentoProduct->getEventEndDate()) {
                    if (strtotime($this->getDateOnly($this->getMagentoDate())) >= strtotime($this->getDateOnly($eventStartDate)) && strtotime($this->getDateOnly($this->getMagentoDate())) <= strtotime($this->getDateOnly($eventEndDate))) {
                        $eventCost = $magentoProduct->getEventCost();
                    }
                    else {
                        $eventCost = 0;
                    }
                }
                else {
                    $eventCost = 0;
                }
            }
        }
        else {
            $eventCost = 0;
        }

        $customFieldEventCost = new StringCustomFieldRef();
        $customFieldEventCost->scriptId = 'custitem_eventcost';
        $customFieldEventCost->value = utf8_encode($eventCost);
        $customFieldList->customField[] = $customFieldEventCost;

        $inventoryItem->customFieldList = $customFieldList;

        return $inventoryItem;
    }
    
    public function getMagentoDate() {
        return date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(time()));
    }

    public function getDateOnly($date) {
        return date('Y-m-d', strtotime($date));
    }
}
