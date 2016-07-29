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
    public abstract function isMagentoImportable($record);

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

        // if current record type is request order
        if ($this->getRecordType() == 'requestorder')
        {
            $response = Mage::helper('rocketweb_netsuite/mapper_requestorder')->getRequestOrder();
            if (is_array($response))
                return $response;
            else
                return false;
        }

        if (is_null($response)) {
            $searchRequest = $this->getNetsuiteRequest($this->getRecordType(),$startDateTime);
            $response = $netsuiteService->search($searchRequest);
            //$this->log("searchRequest: " . json_encode($searchRequest));
            //$this->log("response: " . json_encode($response));

            if ($response->searchResult->status->isSuccess) {
                if ($this->getRecordType() == RecordType::inventoryItem)
                    return $response->searchResult->recordList->record;
                else
                    return $response->searchResult->searchRowList->searchRow;
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
                if ($this->getRecordType() == RecordType::inventoryItem)
                    return $searchResponse->searchResult->recordList->record;
                else
                    return $searchResponse->searchResult->searchRowList->searchRow;
            }
            else {
                //throw new Exception((string) print_r($searchResponse->searchResult->status->statusDetail, true));
                $bodyEmail = "totalPages: " . $totalPages . " currentPage: " . $currentPage . "  [failed] -> " . $searchResponse->searchResult->status->statusDetail;
                $subjectEmail = "Error Notification on processing NS Connector";
                $this->__sendEmail($bodyEmail, $subjectEmail);
            }
        }
    }

    private function __sendEmail($body="", $subject)
    {
        $mail = Mage::getModel('core/email');
        $mail->setToName('Uke Mayendra');
        $mail->setToEmail('uke.m@bilna.com');
        $mail->setBody($body);
        $mail->setSubject($subject);
        $mail->setFromEmail('taufik.r@bilna.com');
        $mail->setFromName("Magento NS Connector");
        $mail->setType('text');
        try {
            $mail->send();
        }catch (Exception $e) {
            Mage::log("Unable to send Email", null, "email_error.log");
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

        // if current request is inventory item, use transaction search basic to get all items
        if ($this->getRecordType() == RecordType::inventoryItem)
        {
            $typeField = new SearchEnumMultiSelectField();
            $typeField->operator = SearchEnumMultiSelectFieldOperator::anyOf;
            $typeField->searchValue = $recordType;

            $tranSearchBasic = new TransactionSearchBasic();
            $tranSearchBasic->lastModifiedDate = $searchDateField;
            $tranSearchBasic->type = $typeField;

            Mage::dispatchEvent('netsuite_import_request_before', array ('record_type' => $this->getRecordType(), 'search_object' => $tranSearchBasic));

            $searchRequest = new SearchRequest();
            $searchRequest->searchRecord = $tranSearchBasic;
        }
        // else, use transaction search advanced to get some items
        // we will call getRequest anyway, so no need to return the whole items for non-inventoryitem records
        else
        {
            $tsa = new TransactionSearchAdvanced();

            $tsa->columns = new TransactionSearchRow();
            $tsa->columns->basic = new TransactionSearchRowBasic();
            /* we want to return the values of createdFrom, internalId, searchId, lastModifiedDate
            and dateCreated only */
            $tsa->columns->basic->createdFrom = new SearchColumnSelectField();
            $tsa->columns->basic->internalId = new SearchColumnSelectField();
            $tsa->columns->basic->searchId = new SearchColumnSelectField();
            $tsa->columns->basic->lastModifiedDate = new SearchColumnDateField();
            $tsa->columns->basic->dateCreated = new SearchColumnDateField();

            if ($this->getRecordType() == RecordType::invoice || $this->getRecordType() == RecordType::salesOrder || $this->getRecordType() == RecordType::itemFulfillment)
            {
                $colArr = new SearchColumnSelectCustomField();
                $colArr->internalId = 'custbody_sourcero';
                if ($this->getRecordType() == RecordType::salesOrder || $this->getRecordType() == RecordType::invoice)
                {
                    $col2Arr = new SearchColumnSelectCustomField();
                    $col2Arr->internalId = 'custbody_paymentmethod';
                    $tsa->columns->basic->customFieldList->customField = array($colArr, $col2Arr);
                }
                else
                if ($this->getRecordType() == RecordType::itemFulfillment)
                {
                    $col2Arr = new SearchColumnStringCustomField();
                    $col2Arr->internalId = 'custbody_deliverytype';
                    $tsa->columns->basic->customFieldList->customField = array($colArr, $col2Arr);
                }
                else
                    $tsa->columns->basic->customFieldList->customField = array($colArr);
            }

            $tsa->criteria = new TransactionSearch();
            $tsa->criteria->basic = new TransactionSearchBasic();

            $tsa->criteria->basic->mainLine = new SearchBooleanField();
            $tsa->criteria->basic->mainLine->searchValue = true;
            $tsa->criteria->basic->mainLine->searchValueSpecified = true;

            $tsa->criteria->basic->lastModifiedDate = $searchDateField;

            $tsa->criteria->basic->type = new SearchEnumMultiSelectField();
            $tsa->criteria->basic->type->operator = SearchEnumMultiSelectFieldOperator::anyOf;
            $tsa->criteria->basic->type->operatorSpecified = true;
            $tsa->criteria->basic->type->searchValue = $recordType;

            Mage::dispatchEvent('netsuite_import_request_before', array ('record_type' => $this->getRecordType(), 'search_object' => $tsa));

            $searchRequest = new SearchRequest();
            $searchRequest->searchRecord = $tsa;
        }

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