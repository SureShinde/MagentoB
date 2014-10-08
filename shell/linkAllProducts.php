<?php

require_once '../abstract.php';

class RocketWeb_Netsuite_Shell_Productlinker extends Mage_Shell_Abstract
{
    const PRODUCTS_PER_PAGE = 100;

    protected $netsuiteRequest = null;
    protected $netsuiteService = null;
    protected $resumeAt = null;

    protected $skuField = 'custitem_skubilna';

    public function run()
    {
        ini_set('display_errors','on');
        if($this->getArg('help')) {
            echo $this->usageHelp();
            return;
        }

        $this->initImport();
        $this->initNetsuiteRequest();

        $this->logProgress('Grabbing first page...');


        //first page
        $response = $this->netsuiteService->search($this->netsuiteRequest);
        if($response->searchResult->status->isSuccess) {
            if(is_null($this->resumeAt) || $this->resumeAt <=1) {
                $this->logProgress("Page 1");
                foreach($response->searchResult->recordList->record as $inventoryItem) {
                    $this->logProgress('.',false);
                    try {
                        $this->linkMagentoProduct($inventoryItem);
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
                $this->logProgress('.',false);
                try {
                    $this->linkMagentoProduct($inventoryItem);
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

    protected function linkMagentoProduct(InventoryItem $inventoryItem) {
        foreach($inventoryItem->customFieldList->customField as $customField) {
            if($customField->internalId == $this->skuField) {
                /* @var Mage_Catalog_Model_Product $magentoProduct */
                $magentoProduct = $this->getMagentoProductBySku($customField->value);
                if(is_null($magentoProduct)) {
                    $this->logProgress("SKU {$customField->value} not found in Magento.",true);
                }
                else {
                    $magentoProduct->setNetsuiteInternalId($inventoryItem->internalId);
                    $magentoProduct->getResource()->saveAttribute($magentoProduct,'netsuite_internal_id');
                }
            }
        }
    }

    /**
     * @param $sku
     * @return null
     */
    protected function getMagentoProductBySku($sku) {
        $_product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
        if($_product && $_product->getId()) {
            return $_product;
        }
        else {
            return null;
        }
    }

    protected function initNetsuiteRequest() {
        $this->netsuiteRequest = Mage::getModel('rocketweb_netsuite/process_import_inventoryitem')->getNetsuiteRequest(null,RecordType::inventoryItem);
        unset($this->netsuiteRequest->searchRecord->lastModifiedDate);
        unset($this->netsuiteRequest->searchRecord->type);
        $typeField = new SearchEnumMultiSelectField();
        $typeField->operator = SearchEnumMultiSelectFieldOperator::anyOf;
        $typeField->searchValue = RecordType::inventoryItem;
        $this->netsuiteRequest->searchRecord->type = $typeField;

    }


    protected function logProgress($message,$addNewLine = true) {
        if($this->getArg('verbose')) {
            echo $message;
            if($addNewLine) echo "\n";
        }
    }

    protected function initImport() {
        if($this->getArg('resume-at')) {
            $this->resumeAt = (int) $this->getArg('resume-at');
        }

        Mage::dispatchEvent('netsuite_process_import_start_before',array());
    }
}

$shell = new RocketWeb_Netsuite_Shell_Productlinker();
$shell->run();