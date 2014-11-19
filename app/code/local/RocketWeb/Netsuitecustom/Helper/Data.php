<?php
class RocketWeb_Netsuitecustom_Helper_Data extends Mage_Core_Helper_Abstract {
    public function getCustomRecord($recordTypeId,$recordId) {
        static $_customRecordListCache = array();
        if(!isset($_customRecordListCache[$recordTypeId])) {
            
            $netsuiteService = Mage::helper('rocketweb_netsuite')->getNetsuiteService();

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
                $currentPage    = $response->searchResult->pageIndex;
                $totalPages     = $response->searchResult->totalPages;
                $searchId               = $response->searchResult->searchId;
                while ($currentPage <= $totalPages){
                    /*more request*/
                    $currentPage++;
                    $request    = new SearchMoreWithIdRequest();
                    $request->searchId      = $response->searchResult->searchId;
                    $request->pageIndex = $currentPage;
                    $moreSearchResponse = $netsuiteService->searchMoreWithId($request);
                    if($moreSearchResponse->searchResult->status->isSuccess) {
                        foreach($moreSearchResponse->searchResult->recordList->record as $record) {
                            $_customRecordListCache[$recordTypeId][$record->internalId] = $record->name;
                        }
                        $searchMoreReq = new SearchMoreWithIdRequest();
                        $searchMoreReq->searchId = $searchId;
                        $searchMoreReq->pageIndex = $currentPage;
                    }
                }
            }
            else {
                throw new Exception((string) print_r($response->searchResult->status->statusDetail));
            }
        }
        return $_customRecordListCache[$recordTypeId][$recordId];
    }
}