<?php
/**
 * Description of Bilna_Netsuite_Helper_Data
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Netsuitesync_Helper_Data extends RocketWeb_Netsuite_Helper_Mapper {
    public function getNetsuiteFormatProduct(Mage_Catalog_Model_Product $magentoProduct) {
        if (!$netsuiteInternalId = $magentoProduct->getNetsuiteInternalId()) {
            return false;
        }
        
        $netsuiteService = Mage::helper('rocketweb_netsuite')->getNetsuiteService();
        $inventoryItem = new InventoryItem();
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
