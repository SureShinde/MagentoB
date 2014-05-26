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

            if($response->searchResult->status->isSuccess) {
                foreach($response->searchResult->recordList->record as $record) {
                    $_customRecordListCache[$recordTypeId][$record->internalId] = $record->name;
                }
            }
            else {
                throw new Exception((string) print_r($response->searchResult->status->statusDetail));
            }
        }
        return $_customRecordListCache[$recordTypeId][$recordId];
    }
}