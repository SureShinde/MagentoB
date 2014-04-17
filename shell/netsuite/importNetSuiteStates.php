<?php
//initialize Magento
require_once dirname(__FILE__) . '/../../app/Mage.php';
set_time_limit(0);
error_reporting(E_ALL);
ini_set('display_errors','on');

if (!Mage::isInstalled()) {
    echo "Application is not installed yet, please complete install wizard first.";
    exit;
}
Mage::app('admin');


//get all states from NetSuite
$netsuiteService = Mage::helper('rocketweb_netsuite')->getNetsuiteService();
$getObj = new GetAllRecord();
$getObj->recordType = GetAllRecordType::state;
$request = new GetAllRequest();
$request->record = $getObj;
$response = $netsuiteService->getAll($request);
if($response->getAllResult->status->isSuccess) {
    foreach($response->getAllResult->recordList->record as $netsuiteItem) {
        /* @var State $netsuiteItem*/
        if(!countryHasStatesInMagento($netsuiteItem->country)) {
            addStateToMagento($netsuiteItem);
            echo "Added {$netsuiteItem->fullName} in {$netsuiteItem->country} \n";
        }

    }
}
else {
    echo "Error!";
    var_dump($response);
}

function countryHasStatesInMagento($netsuiteCountryCode) {
    static $countriesArray = array();

    $isoCountryCode = Mage::helper('rocketweb_netsuite/transform')->netsuiteCountryToCountryCode($netsuiteCountryCode);
    if(!$isoCountryCode) throw new Exception("No iso code found for {$netsuiteCountryCode}");

    if(!isset($countriesArray[$isoCountryCode])) {
        $statesCollection = Mage::getModel('directory/region')->getCollection();
        $statesCollection->addFieldToFilter('country_id',$isoCountryCode);
        if($statesCollection->getSize()) {
            $countriesArray[$isoCountryCode] = true;
        }
        else {
            $countriesArray[$isoCountryCode] = false;
        }
    }

    return $countriesArray[$isoCountryCode];
}

function addStateToMagento(State $netsuiteState) {
    $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
    $resource = Mage::getSingleton('core/resource');
    $isoCountryCode = Mage::helper('rocketweb_netsuite/transform')->netsuiteCountryToCountryCode($netsuiteState->country);

    $bind = array(
        'country_id' => $isoCountryCode,
        'code' => $netsuiteState->shortname,
        'default_name' => $netsuiteState->fullName
    );
    $connection->insert($resource->getTableName('directory/country_region'), $bind);

    $regionId = $connection->lastInsertId($resource->getTableName('directory/country_region'));
    $bind = array(
        'locale'    => 'en_US',
        'region_id' => $regionId,
        'name'      => $netsuiteState->fullName
    );
    $connection->insert($resource->getTableName('directory/country_region_name'), $bind);
}