<?php
/**
 * Description of RocketWeb_Netsuite_Helper_Mapper_Productcost
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class RocketWeb_Netsuite_Helper_Mapper_Productcost extends RocketWeb_Netsuite_Helper_Mapper_Product {
    /**
     * @param object $magentoProduct
     * @return object $InventoryItem
     */
    public function getNetsuiteFormat($magentoProduct) {
        $inventoryItem = new InventoryItem();
        $inventoryItem->internalId = $magentoProduct->getNetsuiteInternalId();
        
        $customFieldList = new CustomFieldList();
        $customFieldList->customField[] = $this->getNetsuiteCustomFieldCost($magentoProduct); //- cost
        $customFieldList->customField[] = $this->getNetsuiteCustomFieldExpectedCost($magentoProduct); //- expected cost
        $customFieldList->customField[] = $this->getNetsuiteCustomFieldEventCost($magentoProduct); //- event cost
        
        $inventoryItem->customFieldList = $customFieldList;

        return $inventoryItem;
    }
    
    protected function getNetsuiteCustomFieldCost($magentoProduct) {
        $cost = $magentoProduct->getCost();
        
        if ($cost == null || $cost == 0) {
            $cost = 0;
        }
        
        $customFieldCost = new StringCustomFieldRef();
        $customFieldCost->internalId = 'custitem_cost';
        $customFieldCost->value = utf8_encode($cost);
        
        return $customFieldCost;
    }

    protected function getNetsuiteCustomFieldExpectedCost($magentoProduct) {
        $expectedCost = $magentoProduct->getExpectedCost();

        if ($expectedCost == null || $expectedCost == 0) {
            $expectedCost = 0;
        }

        $customFieldExpectedCost = new StringCustomFieldRef();
        $customFieldExpectedCost->internalId = 'custitem_expectedcost';
        $customFieldExpectedCost->value = utf8_encode($expectedCost);
        
        return $customFieldExpectedCost;
    }
    
    protected function getNetsuiteCustomFieldEventCost($magentoProduct) {
        $eventStartDate = $magentoProduct->getEventStartDate();
        $eventEndDate = $magentoProduct->getEventEndDate();

        if (($eventStartDate) && ($eventEndDate) && (strtotime($this->getDateOnly($this->getMagentoDate())) >= strtotime($this->getDateOnly($eventStartDate)) && strtotime($this->getDateOnly($this->getMagentoDate())) <= strtotime($this->getDateOnly($eventEndDate)))) {
            $eventCost = $magentoProduct->getEventCost();
        }
        else {
            $eventCost = 0;
        }

        $customFieldEventCost = new StringCustomFieldRef();
        $customFieldEventCost->internalId = 'custitem_eventcost';
        $customFieldEventCost->value = utf8_encode($eventCost);
        
        return $customFieldEventCost;
    }

    /**
     * @param type $id
     * @return boolean
     */
    public function deleteProductCostQueue($product) {
        $id = $product->getId();
        $eventEndDate = $this->getDateOnly($product->getEventEndDate());
        $today = $this->getDateOnly($this->getMagentoDate());
        
        if (strtotime($eventEndDate) < strtotime($today)) {        
            $model = Mage::getModel('rocketweb_netsuite/productcost');

            if ($model->setId($product->getId())->delete()) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * @return string
     */
    protected function getMagentoDate() {
        return date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(time()));
    }

    /**
     * @param string $date
     * @return string
     */
    protected function getDateOnly($date) {
        return date('Y-m-d', strtotime($date));
    }
}
