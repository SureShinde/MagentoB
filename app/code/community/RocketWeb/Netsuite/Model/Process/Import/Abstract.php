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

abstract class RocketWeb_Netsuite_Model_Process_Import_Abstract {
    protected $_logFilename = 'netsuite_import.log';

    //checks whether an entry is Magento importable, i.e. if the associated order also exists in Magento. The orders that
    //are created directly in Net Suite are not to be imported and managed in Magento
    public abstract function isMagentoImportable(Record $record);

    //returns the type of the element to be added in the export/import queue. See the constants in RocketWeb_Netsuite_Model_Queue_Message
    public abstract function getMessageType();

    //returns the type of the element to be added for deletion in the import queue. See the constants in RocketWeb_Netsuite_Model_Queue_Message
    public abstract function getDeleteMessageType();

    //process a record returned from Netsuite to be added to Magento
    public abstract function process(Record $record, $queueData = null);

    //return the RecordType name for this entity
    public abstract function getRecordType();

    public abstract function isActive();

    public abstract function getPermissionName();

    //queries Netsuite for latest modified entries (shipments, invoices etc)
    public function queryNetsuite($startDateTime) {
        static $response = null;
        static $currentPage = 2;

        $permissionName = $this->getPermissionName();
        
        if (trim($permissionName) && !Mage::helper('rocketweb_netsuite/permissions')->isFeatureEnabled($permissionName)) {
            return false;
        }

        $netsuiteService = $this->_getNetsuiteService();
        $this->setSearchPreferences();

        if (is_null($response)) {
            $searchRequest = $this->getNetsuiteRequest($this->getRecordType(),$startDateTime);
            $response = $netsuiteService->search($searchRequest);
            //$this->log("searchRequest: " . json_encode($searchRequest));
            //$this->log("response: " . json_encode($response));
            
            if ($response->searchResult->status->isSuccess) {
                return $response->searchResult->recordList->record;
            }
            else {
                throw new Exception((string) print_r($response->searchResult->status->statusDetail, true));
            }
        }
        else {
            $totalPages = $response->searchResult->totalPages;
            $searchId = $response->searchResult->searchId;

            if ($currentPage > $totalPages) {
                return false;
            }

            $searchMoreRequest = new SearchMoreWithIdRequest();
            $searchMoreRequest->pageIndex = $currentPage;
            $searchMoreRequest->searchId = $searchId;
            $searchResponse = $netsuiteService->searchMoreWithId($searchMoreRequest);
            $currentPage++;
            
            $this->log("searchMoreRequest: " . json_encode($searchMoreRequest));
            $this->log("searchResponse: " . json_encode($searchResponse));
            
            if ($searchResponse->searchResult->status->isSuccess) {
                return $searchResponse->searchResult->recordList->record;
            }
            else {
                throw new Exception((string) print_r($searchResponse->searchResult->status->statusDetail, true));
            }
        }
    }

    public function queryNetsuiteForDeletedRecords($updatedFrom) {
        $now = new DateTime(Mage::helper('rocketweb_netsuite')->getServerTime());

        $searchDateField = new SearchDateField();
        $searchDateField->searchValue = $updatedFrom;
        $searchDateField->searchValue2 = $now->format(DateTime::ISO8601);
        $searchDateField->operator = SearchDateFieldOperator::within;

        $typeField = new SearchEnumMultiSelectField();
        $typeField->operator = SearchEnumMultiSelectFieldOperator::anyOf;
        $typeField->searchValue = $this->getRecordType();

        $getDeletedFilter = new GetDeletedFilter();
        $getDeletedFilter->deletedDate = $searchDateField;
        $getDeletedFilter->type = $typeField;

        $getDeletedRequest = new GetDeletedRequest();
        $getDeletedRequest->getDeletedFilter = $getDeletedFilter;

        $netsuiteService = $this->_getNetsuiteService();
        $this->setSearchPreferences();

        $response = $netsuiteService->getDeleted($getDeletedRequest);
        
        if ($response->getDeletedResult->status->isSuccess) {
            return $response->getDeletedResult->deletedRecordList->deletedRecord;
        }
        else {
            throw new Exception((string) print_r($response->searchResult->status->statusDetail, true));
        }
    }

    protected function _getNetsuiteService() {
        return Mage::helper('rocketweb_netsuite')->getNetsuiteService();
    }

    protected function setSearchPreferences() {
        $this->_getNetsuiteService()->setSearchPreferences(false, 500);
    }

    public function getNetsuiteRequest($recordType,$startDateTime) {
        $now = new DateTime(Mage::helper('rocketweb_netsuite')->getServerTime());

        $searchDateField = new SearchDateField();
        $searchDateField->searchValue = $startDateTime;
        $searchDateField->searchValue2 = $now->format(DateTime::ISO8601);
        $searchDateField->operator = SearchDateFieldOperator::within;

        $typeField = new SearchEnumMultiSelectField();
        $typeField->operator = SearchEnumMultiSelectFieldOperator::anyOf;
        $typeField->searchValue = $recordType;

        $tranSearchBasic = new TransactionSearchBasic();
        $tranSearchBasic->lastModifiedDate = $searchDateField;
        $tranSearchBasic->type = $typeField;

        Mage::dispatchEvent('netsuite_import_request_before', array ('record_type' => $this->getRecordType(), 'search_object' => $tranSearchBasic));

        $searchRequest = new SearchRequest();
        $searchRequest->searchRecord = $tranSearchBasic;

        return $searchRequest;
    }

    //checks whether the element is already present in Magento
    public function isQueued(RocketWeb_Netsuite_Model_Queue_Message $message) {
        return Mage::helper('rocketweb_netsuite/queue')->messageExistsInQueue($message);
    }
    
    protected function log($message) {
        Mage::helper('rocketweb_netsuite')->log($message, $this->_logFilename);
    }
}