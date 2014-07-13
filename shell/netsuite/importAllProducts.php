<?php

require_once '../abstract.php';

class RocketWeb_Netsuite_Shell_Productimporter extends Mage_Shell_Abstract
{
    const IMPORT_MODE_ALL = 'all';
    const IMPORT_MODE_ADD_ONLY = 'add-only';
    const PRODUCTS_PER_PAGE = 100;

    protected $netsuiteRequest = null;
    protected $netsuiteService = null;
    protected $importMode = null;
    protected $wipeExisting = false;
    protected $resumeAt = null;

    public function run()
    {
        ini_set('display_errors','on');
        if($this->getArg('help')) {
            echo $this->usageHelp();
            return;
        }
        if(!$this->getArg('all') && !$this->getArg('add-only')) {
            echo 'Either "all" or "add-only" must be specified as an import type.';
            return;
        }
        $this->initImport();
        Mage::helper('rocketweb_netsuite')->cacheStandardLists();
        $this->initNetsuiteRequest();


        if($this->wipeExisting) {
            $this->wipeExistingProducts();
        }

        $this->logProgress('Grabbing first page...');


        //first page
        $response = $this->netsuiteService->search($this->netsuiteRequest);
        if($response->searchResult->status->isSuccess) {
            if(is_null($this->resumeAt) || $this->resumeAt <=1) {
                $this->logProgress("Page 1");
                foreach($response->searchResult->recordList->record as $inventoryItem) {
                    if($this->importMode == self::IMPORT_MODE_ADD_ONLY) {
                        if($this->productExists($inventoryItem->internalId)) continue;
                    }
                    $this->logProgress('.',false);
                    try {
                        Mage::getModel('rocketweb_netsuite/process_import_inventoryitem')->process($inventoryItem);
                    }
                    catch(Exception $ex) {
                        var_dump($ex->getMessage());
                    }
                }
                $this->logProgress("\n",false);
            }
        }
        else {
            var_dump($response);
            die();
        }

        //rest of the pages
        $totalPages = $response->searchResult->totalPages;
        $searchId = $response->searchResult->searchId;

        $this->logProgress("Processing $totalPages pages");


        $start = max(2,$this->resumeAt);
        for($i=$start;$i<=$totalPages;$i++) {
            $this->logProgress("Page $i of $totalPages",false);

            $searchMoreRequest = new SearchMoreWithIdRequest();
            $searchMoreRequest->pageIndex = $i;
            $searchMoreRequest->searchId = $searchId;

            $searchResponse = $this->netsuiteService->searchMoreWithId($searchMoreRequest);
            if (!$searchResponse->searchResult->status->isSuccess) {
                throw new Exception((string) print_r($searchResponse->searchResult->status->statusDetail,true));
            }

            $records = $searchResponse->searchResult->recordList->record;
            foreach($records as $inventoryItem) {
                if($this->importMode == self::IMPORT_MODE_ADD_ONLY) {
                    if($this->productExists($inventoryItem->internalId)) continue;
                }
                $this->logProgress('.',false);
                try {
                    Mage::getModel('rocketweb_netsuite/process_import_inventoryitem')->process($inventoryItem);
                }
                catch(Exception $ex) {
                    var_dump($ex->getMessage());
                }
            }

            $this->logProgress("\n",false);
        }

    }
    public function usageHelp()
    {
        return <<<USAGE
Usage:  php -f importAllProducts.php -- [options]
  help                 This help
  verbose              Print progress information
  all                  Import all products
  add-only             Only add new products, do not change the existing ones
  wipe-existing        Delete all products with an internal_netsuite_id before starting the import
  from-date YYYY-MM-DD Import products that were changed/added after the specified date
  resume-at NUM        The page number to resume at
USAGE;
    }

    public function __construct()
    {
        parent::__construct();

        Mage::helper('rocketweb_netsuite')->loadNetsuiteNamespace();
        $this->netsuiteService = Mage::helper('rocketweb_netsuite')->getNetsuiteService();
        $this->netsuiteService->setSearchPreferences(false,self::PRODUCTS_PER_PAGE);
    }

    protected function initNetsuiteRequest() {
        $this->netsuiteRequest = Mage::getModel('rocketweb_netsuite/process_import_inventoryitem')->getNetsuiteRequest(null,RecordType::inventoryItem);
        unset($this->netsuiteRequest->searchRecord->lastModifiedDate);
        unset($this->netsuiteRequest->searchRecord->type);
        $typeField = new SearchEnumMultiSelectField();
        $typeField->operator = SearchEnumMultiSelectFieldOperator::anyOf;
        $typeField->searchValue = RecordType::inventoryItem;
        $this->netsuiteRequest->searchRecord->type = $typeField;



/* $iField = new SearchMultiSelectField();
        $iField->searchValue = new RecordRef();
        $iField->searchValue->internalId = 41292;
        $iField->operator = SearchMultiSelectFieldOperator::anyOf;
        $this->netsuiteRequest->searchRecord->internalId = $iField;*/

        if($this->getArg('from-date')) {
            $dateString = $this->getArg('from-date');

            $parsedDate = date_parse($dateString);
            if(!checkdate($parsedDate['month'],$parsedDate['day'],$parsedDate['year'])) {
                echo "From date cannot be parsed. Make sure it is in the YYYY-MM-DD format";
                exit;
            }
            else {
                $updatedFrom = new DateTime($dateString);
                $updatedFrom = $updatedFrom->format(DateTime::ISO8601);
                $now = new DateTime(Mage::helper('rocketweb_netsuite')->getServerTime());

                $searchDateField = new SearchDateField();
                $searchDateField->searchValue = $updatedFrom;
                $searchDateField->searchValue2 = $now->format(DateTime::ISO8601);
                $searchDateField->operator = SearchDateFieldOperator::within;

                $this->netsuiteRequest->searchRecord->lastModifiedDate = $searchDateField;
            }
        }
    }

    protected function wipeExistingProducts() {
        $productCollection = Mage::getModel('catalog/product')->getCollection();
        $productCollection->addAttributeToFilter('netsuite_internal_id',array('neq'=>''));
        foreach($productCollection as $product) {
            if($product->getNetsuiteInternalId()) {
                $product->delete();
            }
        }
    }

    protected function productExists($netsuiteInternalId) {
        $product = Mage::getModel('catalog/product')->loadByAttribute('netsuite_internal_id',$netsuiteInternalId);
        if(is_object($product) && $product->getId()) {
            return true;
        }
        else {
            return false;
        }
    }

    protected function logProgress($message,$addNewLine = true) {
        if($this->getArg('verbose')) {
            echo $message;
            if($addNewLine) echo "\n";
        }
    }

    protected function initImport() {
        if($this->getArg('all')) {
            $this->importMode = self::IMPORT_MODE_ALL;
        }
        else if($this->getArg('add-only')) {
            $this->importMode = self::IMPORT_MODE_ADD_ONLY;
        }

        if($this->getArg('wipe-existing')) {
            $this->wipeExisting = true;
        }
        if($this->getArg('resume-at')) {
            $this->resumeAt = (int) $this->getArg('resume-at');
        }

        Mage::dispatchEvent('netsuite_process_import_start_before',array());
    }
}

$shell = new RocketWeb_Netsuite_Shell_Productimporter();
$shell->run();
