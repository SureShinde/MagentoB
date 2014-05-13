<?php
/**
 * Description of Bilna_Netsuitesync_Shell_Update_Product
 *
 * @author Bilna Development Team <development@bilna.com>
 */

require_once dirname(__FILE__) . '/../abstract.php';
require_once dirname(__FILE__) . '/../../lib/Netsuite/NetSuiteService.php';
//require_once '../lib/Netsuite/NetSuiteService.php';

class Bilna_Netsuitesync_Shell_Update_Product extends Mage_Shell_Abstract {
    public function run() {
        $this->writeLog("Started update products...");
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
        
        $_netsuiteService = new NetSuiteService();
        $_productCollection = Mage::getModel('catalog/product')->getCollection()->addAttributeToSelect('entity_id');
        $this->writeLog("Processing {$_productCollection->getSize()} products ...");
        
        $counter = 0;
        $resumeAt = $this->getArg('resume-at') ? (int) $this->getArg('resume-at') : 0;
        
        foreach ($_productCollection as $_product) {
            if ($counter < $resumeAt) {
                $counter++;
                continue;
            }
            
            try {
                $_magentoProduct = Mage::getModel('catalog/product')->load($_product->getEntityId());
                
                if ($_magentoProduct->getNetsuiteInternalId()) {
                    $request = new UpdateRequest();
                    $request->record = $this->getNetsuiteFormatProduct($_magentoProduct);
                    $response = $_netsuiteService->update($request);

                    if ($response->writeResponse->status->isSuccess) {
                        $netsuiteId = $response->writeResponse->baseRef->internalId;
                        $this->writeLog("success update product #" . $netsuiteId);
                    }
                    else {
                        $this->writeLog(json_encode($response));
                        $this->writeLog("failed update product #" . $_product->getEntityId());
                    }
                }
                else {
                    //$this->writeLog("cannot load netsuite_internal_id from product #" . $_product->getEntityId());
                }
            }
            catch (Exception $e) {
                $this->writeLog("Error updating product #" . $_product->getEntityId() . ": " . $e->getMessage());
            }
            
            $counter++;
        }
        
        $this->writeLog("Ended update products...");
    }
    
    public function getNetsuiteFormatProduct(Mage_Catalog_Model_Product $magentoProduct) {
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
    
    private function writeLog($message) {
        @error_log($message . "\n", 3, "/tmp/netsuiteUpdateProduct.log");
    }
}

$shell = new Bilna_Netsuitesync_Shell_Update_Product();
$shell->run();
