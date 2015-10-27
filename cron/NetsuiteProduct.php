<?php
ini_set('display_errors', 1);
error_reporting(E_ALL ^ E_NOTICE);

require_once '../lib/Netsuite/NetSuiteService.php';
require_once '../app/Mage.php';

writeLog("Started update products...");

Mage::app();
$netsuiteService = new NetSuiteService();

$productCollection = getProductCollection();
writeLog("Processing {$productCollection->getSize()} products ...");

foreach ($productCollection as $product) {
    $isSuccess = false;
    
    if ($product->getProductId()) {
        $response = updateProduct($netsuiteService, $product);

        if ($response->writeResponse->status->isSuccess) {
            $isSuccess = true;
            $netsuiteId = $response->writeResponse->baseRef->internalId;
            writeLog("success update product #" . $product->getProductId() . " with internalId #" . $netsuiteId);
        }
        else {
            writeLog(json_encode($response));
            writeLog("failed update product #" . $product->getProductId());
        }
    }
    else {
        writeLog("cannot load netsuite_internal_id from product #" . $product->getProductId());
    }
    
    if ($isSuccess) {
        deleteProductCostQueue($product->getId());
    }
}

writeLog("Ended update products...");
exit;

function getProductCollection() {
    $productCollection = Mage::getModel('rocketweb_netsuite/productcost')->getCollection();
    
    return $productCollection;
}
    
function updateProduct($netsuiteService, $magentoProduct) {
    $response = false;

    if ($magentoProduct) {
        $netsuiteProduct = getNetsuiteFormatProduct($magentoProduct);

        $request = new UpdateRequest();
        $request->record = $netsuiteProduct;
        $response = $netsuiteService->update($request);
        writeLog("request: " . json_encode($request));
        writeLog("response: " . json_encode($response));
    }

    return $response;
}

function getNetsuiteFormatProduct($magentoProduct) {
    $inventoryItem = new InventoryItem();
    $inventoryItem->internalId = $magentoProduct->getNetsuiteInternalId();
    $customFieldList = new CustomFieldList();

    //- expected cost
    $expectedCost = $magentoProduct->getExpectedCost();
    
    if ($expectedCost == null || $expectedCost == 0) {
        $expectedCost = 0;
    }

    $customFieldExpectedCost = new StringCustomFieldRef();
    $customFieldExpectedCost->scriptId = 'custitem_expectedcost';
    $customFieldExpectedCost->value = utf8_encode($expectedCost);
    $customFieldList->customField[] = $customFieldExpectedCost;

    //- event cost
    $eventStartDate = $magentoProduct->getEventStartDate();
    $eventEndDate = $magentoProduct->getEventEndDate();
    
    if (($eventStartDate) && ($eventEndDate) && (strtotime(getDateOnly(getMagentoDate())) >= strtotime(getDateOnly($eventStartDate)) && strtotime(getDateOnly(getMagentoDate())) <= strtotime(getDateOnly($eventEndDate)))) {
        $eventCost = $magentoProduct->getEventCost();
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

function deleteProductCostQueue($id) {
    $model = Mage::getModel('rocketweb_netsuite/productcost');
    
    try {
        $model->setId($id)->delete();
        Mage::log("delete queueProductSaveCostNetsuite #" . $id . " : successfully", null, "netsuite.log");
    }
    catch (Exception $e){
        Mage::log("delete queueProductSaveCostNetsuite #" . $id . " : failed, " . $e->getMessage(), null, "netsuite.log");
    }
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
