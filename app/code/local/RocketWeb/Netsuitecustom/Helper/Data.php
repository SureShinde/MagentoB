<?php
class RocketWeb_Netsuitecustom_Helper_Data extends Mage_Core_Helper_Abstract {
    public function getCustomRecord($recordTypeId,$recordId) {
        static $_customRecordListCache = array();
        if(!isset($_customRecordListCache[$recordTypeId])) {
            static $response = null;
            static $currentPage = 2;
            
            $netsuiteService = Mage::helper('rocketweb_netsuite')->getNetsuiteService();

            if (is_null($response)) {

                $customRecordSearchCriteria = new CustomRecordSearchBasic();
                $customRecordSearchCriteria->recType = new RecordRef();
                $customRecordSearchCriteria->recType->internalId = $recordTypeId;

                $request = new SearchRequest();
                $request->searchRecord = $customRecordSearchCriteria;
        
                $response = $netsuiteService->search($request);
    //error_log("\nRecordTypeId ".print_r($recordTypeId,1), 3, '/tmp/netResponse.log');
    //error_log("\nResponse ".print_r($response,1), 3, '/tmp/netResponse.log');
                if($response->searchResult->status->isSuccess) {
                    foreach($response->searchResult->recordList->record as $record) {
                        $_customRecordListCache[$recordTypeId][$record->internalId] = $record->name;
                    }
                }
                else {
                    throw new Exception((string) print_r($response->searchResult->status->statusDetail));
                }
            }else{
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

                if($searchResponse->searchResult->status->isSuccess) {
                    foreach($searchResponse->searchResult->recordList->record as $record) {
                        $_customRecordListCache[$recordTypeId][$record->internalId] = $record->name;
                    }
                }
                else {
                    throw new Exception((string) print_r($searchResponse->searchResult->status->statusDetail));
                }
            }
        }
        return $_customRecordListCache[$recordTypeId][$recordId];
    }
}