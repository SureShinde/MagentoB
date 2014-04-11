<?php
/**
 * Rocket Web Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is available through the world-wide-web at this URL:
 * http://www.rocketweb.com/RW-LICENSE.txt
 *
 * @category   RocketWeb
 * @package    RocketWeb_Netsuite
 * @copyright  Copyright (c) 2013 RocketWeb (http://www.rocketweb.com)
 * @author     Rocket Web Inc.
 * @license    http://www.rocketweb.com/RW-LICENSE.txt
 */
class RocketWeb_Netsuite_Helper_Stock extends Mage_Core_Helper_Data {
    const LAST_STOCK_UPDATE_FLAG_NAME = 'last_stock_update_date';


    public function getLastStockUpdateDate() {
        return Mage::getModel('core/flag',array('flag_code'=>self::LAST_STOCK_UPDATE_FLAG_NAME))->loadSelf()->getFlagData();
    }

    public function shouldRun($nowAsSqlDate) {
        //Net Suite integration disabled?
        if(!Mage::helper('rocketweb_netsuite')->isEnabled()) {
            return false;
        }

        //stock updates disabled?
        if(!Mage::helper('rocketweb_netsuite/permissions')->isFeatureEnabled(RocketWeb_Netsuite_Helper_Permissions::GET_STOCK_UPDATES)) {
            return false;
        }

        //stock update never ran
        if(!$this->getLastStockUpdateDate()) {
            return true;
        }
        else {
            $lastStockUpdateDate = $this->getLastStockUpdateDate();

            $updateEveryNHours = $this->getUpdateAtNHours();
            if($updateEveryNHours) {
                $lastStockUpdateDate = strtotime($lastStockUpdateDate);
                $nextUpdateDate = mktime(date('H',$lastStockUpdateDate)+$updateEveryNHours,date('i',$lastStockUpdateDate),
                                            date('s',$lastStockUpdateDate),date('m',$lastStockUpdateDate),date('d',$lastStockUpdateDate),
                                            date('Y',$lastStockUpdateDate));
                if($nextUpdateDate <= strtotime($nowAsSqlDate)) {
                    return true;
                }
                else {
                    return false;
                    //return true; //THIS IS ONLY FOR TESTING!!!!!
                }
            }
            else {
                return true;
            }

        }
    }

    public function setLastUpdateDate($netsuiteDateString) {
        $netsuiteDateString = Mage::helper('rocketweb_netsuite')->convertNetsuiteDateToSqlFormat($netsuiteDateString);
        $flagModel = Mage::getModel('core/flag',array('flag_code'=>self::LAST_STOCK_UPDATE_FLAG_NAME))->loadSelf();
        $flagModel->setFlagData($netsuiteDateString);
        $flagModel->save();
    }

    public function processStockUpdates($logger = null) {
        $numUpdated = 0;

        $savedSearchId = $this->getStockLevelsSavedSearchId();
        $pageSize = $this->getStockLevelsSavedSearchPageSize();
        $netsuiteService = Mage::helper('rocketweb_netsuite')->getNetsuiteService();
        $netsuiteService->setSearchPreferences(true,$pageSize);

        $search = new ItemSearchAdvanced();
        $search->savedSearchId = $savedSearchId;

        $request = new SearchRequest();
        $request->searchRecord = $search;

        if($logger) $logger->logProgress("Getting page 1");

        $searchResponse = $netsuiteService->search($request);
        if (!$searchResponse->searchResult->status->isSuccess) {
            throw new Exception((string) print_r($searchResponse->searchResult->status->statusDetail,true));
        }

        $records = $searchResponse->searchResult->searchRowList->searchRow;
        foreach($records as $record) {
            $this->processSingleStockUpdate($record->basic);
            if($logger) $logger->logProgress(".");
            $numUpdated++;;
        }
        if($logger) $logger->logProgress("\n");

        //rest of the pages
        $totalPages = $searchResponse->searchResult->totalPages;
        $searchId = $searchResponse->searchResult->searchId;
        for($i=2;$i<=$totalPages;$i++) {
            if($logger) $logger->logProgress("Getting page $i out of $totalPages");

            $searchMoreRequest = new SearchMoreWithIdRequest();
            $searchMoreRequest->pageIndex = $i;
            $searchMoreRequest->searchId = $searchId;

            $searchResponse = $netsuiteService->searchMoreWithId($searchMoreRequest);
            if (!$searchResponse->searchResult->status->isSuccess) {
                throw new Exception((string) print_r($searchResponse->searchResult->status->statusDetail,true));
            }

            $records = $searchResponse->searchResult->searchRowList->searchRow;
            foreach($records as $record) {
                $this->processSingleStockUpdate($record->basic);
                if($logger) $logger->logProgress(".");
                $numUpdated++;;
            }

            if($logger) $logger->logProgress("\n");
        }

        if(Mage::helper('rocketweb_netsuite/changelog')->isChangeLogEnabled()) {
            Mage::helper('rocketweb_netsuite/changelog')->logChange(RocketWeb_Netsuite_Model_Changelog::STOCK_ADJUSTMENT,'n/a',"$numUpdated items processed as stock updates");
        }

        $this->clearCache();

    }

    protected function clearCache() {
        Mage::app()->cleanCache();
    }

    public function processSingleStockUpdate(ItemSearchRowBasic $itemSearchRow) {

        $internalId = $itemSearchRow->internalId[0]->searchValue->internalId;
        $newQty = $this->getQtyFromSearchRow($itemSearchRow);

        if($internalId) {
            $magentoProduct = Mage::getModel('catalog/product')->loadByAttribute('netsuite_internal_id',$internalId);
            if($magentoProduct) {
                $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($magentoProduct->getId());
                if($stockItem) {
                    $stockItem->setQty($newQty);
                    if($newQty>=1) $stockItem->setIsInStock(1);
                    else $stockItem->setIsInStock(0);

                    Mage::dispatchEvent('netsuite_stock_item_save_before',array('stock_item'=>$stockItem,'item_search_row'=>$itemSearchRow));
                    $stockItem->save();
                }
            }
        }
    }

    protected function getQtyFromSearchRow(ItemSearchRowBasic $itemSearchRow) {
        static $fieldName = null;
        static $fieldType = null;
        if(is_null($fieldName)) {
            $fieldName = Mage::getStoreConfig('rocketweb_netsuite/stock/qty_field_name');
            $fieldType = Mage::getStoreConfig('rocketweb_netsuite/stock/qty_field_type');
        }

        switch($fieldType) {
            case RocketWeb_Netsuite_Model_Adminhtml_System_Config_Source_Fieldtype::FIELD_TYPE_STANDARD:
                return $itemSearchRow->{$fieldName}[0]->searchValue;
            case RocketWeb_Netsuite_Model_Adminhtml_System_Config_Source_Fieldtype::FIELD_TYPE_CUSTOM:
                foreach($itemSearchRow->customFieldList->customField as $customField) {
                    if($customField->internalId == $fieldName) {
                        return $customField->value;
                    }
                }
                break;
        }
    }

    public function getUpdateAtNHours() {
        return Mage::getStoreConfig('rocketweb_netsuite/stock/update_stocks_every_n_hours');
    }
    public function getStockLevelsSavedSearchId() {
        return Mage::getStoreConfig('rocketweb_netsuite/stock/custom_search_id');
    }
    public function getStockLevelsSavedSearchPageSize() {
        return Mage::getStoreConfig('rocketweb_netsuite/stock/custom_search_page_size');
    }

}