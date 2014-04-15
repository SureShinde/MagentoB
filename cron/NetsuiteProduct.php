<?php
ini_set('display_errors', 1);
error_reporting(E_ALL ^ E_NOTICE);

require_once '../lib/Netsuite/NetSuiteService.php';
require_once '../app/Mage.php';

writeLog("Started update products...");

Mage::app();
$netsuiteService = new NetSuiteService();

$productCollection = Mage::getModel('catalog/product')->getCollection()->addAttributeToSelect('entity_id');
writeLog("Processing {$productCollection->getSize()} products ...");

foreach ($productCollection as $product) {
    try {
        $magentoProduct = Mage::getModel('catalog/product')->load($product->getEntityId());

        if ($magentoProduct->getNetsuiteInternalId()) {
            $response = updateProduct($netsuiteService, $magentoProduct);

            if ($response->writeResponse->status->isSuccess) {
                $netsuiteId = $response->writeResponse->baseRef->internalId;
                writeLog("success update product #" . $product->getEntityId() . " with internalId #" . $netsuiteId);
            }
            else {
                writeLog(json_encode($response));
                writeLog("failed update product #" . $product->getEntityId());
            }
        }
        else {
            writeLog("cannot load netsuite_internal_id from product #" . $product->getEntityId());
        }
    }
    catch (Exception $e) {
        writeLog("Error updating product #" . $product->getEntityId() . ": " . $e->getMessage());
    }
}

writeLog("Ended update products...");
exit;
    
function updateProduct($netsuiteService, $magentoProduct) {
    $response = false;

    if ($magentoProduct) {
        $netsuiteProduct = getNetsuiteFormatProduct($magentoProduct);

        $request = new UpdateRequest();
        $request->record = $netsuiteProduct;
        $response = $netsuiteService->update($request);
    }

    return $response;
}

function getNetsuiteFormatProduct($magentoProduct) {
    $inventoryItem = new InventoryItem();
    $inventoryItem->internalId = $magentoProduct->getNetsuiteInternalId();
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
                if (strtotime(getDateOnly(getMagentoDate())) >= strtotime(getDateOnly($eventStartDate)) && strtotime(getDateOnly(getMagentoDate())) <= strtotime(getDateOnly($eventEndDate))) {
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

function getMagentoDate() {
    return date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(time()));
}

function getDateOnly($date) {
    return date('Y-m-d', strtotime($date));
}

function writeLog($message) {
    @error_log($message . "\n", 3, "/tmp/netsuiteUpdateProduct.log");
}
