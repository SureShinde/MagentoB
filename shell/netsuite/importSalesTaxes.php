<?php
require_once dirname(__FILE__) . '/../../app/Mage.php';
set_time_limit(0);

error_reporting(E_ALL);
ini_set('display_errors','on');

if (!Mage::isInstalled()) {
    echo "Application is not installed yet, please complete install wizard first.";
    exit;
}

Mage::app('admin');

$defaultCustomerClasses = explode(',',Mage::getStoreConfig('rocketweb_netsuite/tax_rates/customer_classes'));
$defaultProductClasses = explode(',',Mage::getStoreConfig('rocketweb_netsuite/tax_rates/product_classes'));

$taxRuleCollection = Mage::getModel('tax/calculation_rule')->getCollection();
$taxRuleCollection->addFieldToFilter(array('netsuite_internal_id','netsuite_internal_id'),array(array('neq'=>0),array('neq'=>'NULL')));
$taxRuleCollection->load();
if($taxRuleCollection->getSize()) {
    foreach($taxRuleCollection as $ruleItem) {
        $ruleItem->delete();
    }
}

$taxRateCollection = Mage::getModel('tax/calculation_rate')->getCollection();
$taxRateCollection->addFieldToFilter(array('netsuite_internal_id','netsuite_internal_id'),array(array('neq'=>0),array('neq'=>'NULL')));
$taxRateCollection->load();
if($taxRateCollection->getSize()) {
    foreach($taxRateCollection as $rateItem) {
        $rateItem->delete();
    }
}


$netsuiteService = Mage::helper('rocketweb_netsuite')->getNetsuiteService();
$getObj = new GetAllRecord();
$getObj->recordType = GetAllRecordType::taxGroup;
$request = new GetAllRequest();
$request->record = $getObj;

$response = $netsuiteService->getAll($request);
if($response->getAllResult->status->isSuccess) {
    foreach($response->getAllResult->recordList->record as $netsuiteItem) {

        $zipCodes = extractZipCodes($netsuiteItem->city);
        $city = extractCity($netsuiteItem->city);
        if(!count($zipCodes)) continue;
        if($netsuiteItem->rate<=0) continue;

        $currentTaxItems = array();

        foreach($zipCodes as $zip) {
            if(!is_numeric($zip) || $zip == 0) continue;

            $taxCode = $netsuiteItem->itemId;

            $taxItem = Mage::getModel('tax/calculation_rate');
            $taxItem->setTaxCountryId('US');
            if(!$netsuiteItem->state) {
                $taxItem->setTaxRegionId(0);
            }
            else {
                $regionModel = Mage::getModel('directory/region')->loadByCode($netsuiteItem->state, 'US');
                if($regionModel->getId()) {
                    $taxItem->setTaxRegionId($regionModel->getId());
                }
                else {
                    continue;
                }
            }
            $taxCode.='-'.$zip;
            $taxItem->setTaxPostcode($zip);

            $taxItem->setCode($taxCode);
            $taxItem->setRate($netsuiteItem->rate);
            $taxItem->setTaxCity($city);
            $taxItem->setNetsuiteInternalId($netsuiteItem->internalId);
            $taxItem->save();

            $currentTaxItems[] = $taxItem->getId();
        }

        $taxRule = Mage::getModel('tax/calculation_rule');
        $taxRule->setCode($netsuiteItem->itemId);
        $taxRule->setTaxRate($currentTaxItems);
        $taxRule->setTaxCustomerClass($defaultCustomerClasses);
        $taxRule->setTaxProductClass($defaultProductClasses);
        $taxRule->setNetsuiteInternalId($netsuiteItem->internalId);
        $taxRule->setPriority(0);
        $taxRule->setPosition(0);
        $taxRule->save();

        echo '.';
    }
    echo 'Done';
}
else {
    echo "Error!";
    var_dump($response);
}

function extractCity($string) {
    return substr($string,0,strpos($string,'_'));
}

function extractZipCodes($string) { //example: ALBANY_94706,ALBANY_94707,ALBANY_94710
    $zipCodes = array();
    $pieces = explode(',',$string);

    foreach($pieces as $piece) {
        $zipCodes[]=preg_replace('/[^_]*_/','',$piece);
    }

    return array_unique($zipCodes);
}