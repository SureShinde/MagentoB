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

class RocketWeb_Netsuite_Model_Process {
    protected $_processedOperatios = array ();
	
    public function processExport($logger = null) {
        if (!Mage::helper('rocketweb_netsuite')->isEnabled()) {
            return;
        }
		
        $maxMessages = (int) Mage::getStoreConfig('rocketweb_netsuite/queue_processing/export_batch_size');
        
        if (!$maxMessages) {
            $maxMessages = 50;
        }
		
        $queue = Mage::helper('rocketweb_netsuite/queue')->getQueue(RocketWeb_Netsuite_Helper_Queue::NETSUITE_EXPORT_QUEUE);
        $messages = $queue->receive($maxMessages);
        
        foreach ($messages as $originalMessage) {
            $queueData = array (
                'message_id' => $originalMessage->__get('message_id'),
                'queue_id' => $originalMessage->__get('queue_id'),
                'handle' => $originalMessage->__get('handle'),
                'body' => $originalMessage->body,
                'timeout' => $originalMessage->__get('timeout'),
                'created' => $originalMessage->__get('created'),
                'priority' => $originalMessage->__get('priority')
            );
            $message = Mage::getModel('rocketweb_netsuite/queue_message')->unpack($originalMessage->body, RocketWeb_Netsuite_Helper_Queue::NETSUITE_EXPORT_QUEUE);
            $processModelString = 'rocketweb_netsuite/process_export_' . $message->getAction();
            $processModel = Mage::getModel($processModelString);

            // FDS Check Start (BILNA-1333)
            if(strtolower($message->getAction()) == 'order_place') {
                $logId = 0;
                $fraudModel = Mage::getModel('bilna_fraud/log')->load($message->getEntityId(), 'order_id');
                $logId = $fraudModel->getLogId();

                if(!is_null($logId)) {
                    $queue->deleteMessage($originalMessage);
                    continue;
                }
            }
            // FDS Check End (BILNA-1333)
            
            if (!get_class($processModel)) {
                Mage::helper('rocketweb_netsuite')->log("Action {$message->getAction()} requires model $processModelString");
                continue;
            }
            else {
                try {
                    if (!isset ($this->_processedOperatios[$message->getAction()][$message->getEntityId()])) { //if more of the same operation are on the list, process just one.
                        if ($logger) {
                            $logger->logProgress("Export {$message->getAction()} {$message->getEntityId()} \n");
                        }
                        
                        $processModel->process($message, $queueData);
                        $this->_processedOperatios[$message->getAction()][$message->getEntityId()] = 1;
                    }
                    
                    $queue->deleteMessage($originalMessage);
                }
                catch (Exception $ex) {
                    Mage::helper('rocketweb_netsuite')->log($ex->getMessage());
                }
            }
        }
    }

    public function processImport($logger = null) {
        if (!Mage::helper('rocketweb_netsuite')->isEnabled()) {
            return;
        }
        
        Mage::dispatchEvent('netsuite_process_import_start_before', array ());
        Mage::helper('rocketweb_netsuite')->cacheStandardLists();
        
        $time = Mage::helper('rocketweb_netsuite')->getServerTime();
        $updatedFrom = $this->getUpdatedFromDateInNetsuiteFormat(RocketWeb_Netsuite_Helper_Queue::NETSUITE_IMPORT_QUEUE);
        $importableEntities = $this->getImportableEntities();
        
        foreach ($importableEntities as $path => $name) {
            if ($logger) {
                $logger->logProgress("Importing ".$name." \n");
            }
            
            $importableEntityModel = Mage::getModel('rocketweb_netsuite/process_import_' . $path);
            
            if (!$importableEntityModel) {
                Mage::helper('rocketweb_netsuite')->log("Model class not found for $path");
                continue;
            }
            
            if (!$importableEntityModel instanceof RocketWeb_Netsuite_Model_Process_Import_Abstract) {
                Mage::helper('rocketweb_netsuite')->log("$path is not an instance of RocketWeb_Netsuite_Model_Process_Import_Abstract");
                continue;
            }
            
            if (!$importableEntityModel->isActive()) {
                continue;
            }
            
            while ($records = $importableEntityModel->queryNetsuite($updatedFrom)) {
                if (is_array($records)) {
                    $internalRecordIds = array ();
                    
                    if ($importableEntityModel->getRecordType() != RecordType::inventoryItem) {
                        foreach ($records as $record) {
                            if ($importableEntityModel->isMagentoImportable($record) && !$importableEntityModel->isAlreadyImported($record)) {
                                $internalRecordIds[] = $record->basic->internalId[0]->searchValue->internalId;
                            }
                        }

                        if (is_array($internalRecordIds)) {
                            $listrequest = new GetListRequest();

                            $i = 0;
                            // create array of baseref first to be passed as parameter for GetListRequest
                            foreach ($internalRecordIds as $internalRecordId) {
                                $listrequest->baseRef[$i] = new RecordRef();
                                $listrequest->baseRef[$i]->internalId = $internalRecordId;
                                $listrequest->baseRef[$i]->type = $importableEntityModel->getRecordType();
                                $i++;
                            }

                            $logger->logProgress("request date " . date("Y-m-d H:i:s"));
                            $logger->logProgress("request {$name}: " . json_encode($listrequest));

                            $lastModifiedDate = $updatedFrom;
                            $x=0;
                            $tryNumber = 10;

                            do{
                                ++$x;
                                $getListsResponse = Mage::helper('rocketweb_netsuite')->getNetsuiteService()->getList($listrequest);

                                $logger->logProgress("response date " . date("Y-m-d H:i:s"));
                                $logger->logProgress("response {$name}: " . json_encode($getListsResponse));
                                $logger->logProgress("----------------------------------------------------------------------------------------");

                                $error = true;
                                foreach($getListsResponse->readResponseList->readResponse as $response)
                                {
                                    if(!$response->status->isSuccess)
                                    {
                                        $error = false;
                                        if($x == $tryNumber)
                                        {
                                            throw new Exception((string) print_r($response->status->statusDetail, true));
                                        }
                                        sleep(1);
                                    }
                                    else
                                    {
                                        $record = $response->record;
                                        $x = 0;

                                        $message = Mage::getModel('rocketweb_netsuite/queue_message');
                                        $message = $message->create($importableEntityModel->getMessageType(), $record->internalId, RocketWeb_Netsuite_Helper_Queue::NETSUITE_IMPORT_QUEUE, $record);
                                        
                                        if (!$importableEntityModel->isQueued($message)) {
                                            Mage::helper('rocketweb_netsuite/queue')->getQueue(RocketWeb_Netsuite_Helper_Queue::NETSUITE_IMPORT_QUEUE)->send($message->pack(), Mage::helper('rocketweb_netsuite')->getRecordPriority($path));
                                        }
                                    }
                                
                                }
   
                            }while($error == false && $x <= $tryNumber);
                            
                        }
                        
                        
                    }
                    else {
                        foreach ($records as $record) {
                            if (!$record) {
                                continue;
                            }
                            
                            try{                        
                                //Performance optimization for product import. we do not load each record fully. Will handle missing data differently.
                                $message = Mage::getModel('rocketweb_netsuite/queue_message');
                                $message = $message->create($importableEntityModel->getMessageType(), $record->internalId, RocketWeb_Netsuite_Helper_Queue::NETSUITE_IMPORT_QUEUE, $record);
                            }catch(Exception $e){
                                Mage::helper('rocketweb_netsuite')->log($e->getMessage());
                            }

                            if (!$importableEntityModel->isQueued($message)) {
                                Mage::helper('rocketweb_netsuite/queue')->getQueue(RocketWeb_Netsuite_Helper_Queue::NETSUITE_IMPORT_QUEUE)->send($message->pack(), Mage::helper('rocketweb_netsuite')->getRecordPriority($path));
                            }
                        }
                    }
                }
            }
        }

        Mage::helper('rocketweb_netsuite/queue')->setLastUpdateAccessDate($time, RocketWeb_Netsuite_Helper_Queue::NETSUITE_IMPORT_QUEUE);
        $this->processQueue($logger);
        $this->populateDeleteQueue();
        $this->processDeleteQueue();
    }

    public function processQueueImport($logger = null) {
        if (!Mage::helper('rocketweb_netsuite')->isEnabled()) {
            return;
        }

        //Mage::dispatchEvent('netsuite_process_import_start_before', array ());
        //Mage::helper('rocketweb_netsuite')->cacheStandardLists();
        //$time = Mage::helper('rocketweb_netsuite')->getServerTime();

        //Mage::helper('rocketweb_netsuite/queue')->setLastUpdateAccessDate($time, RocketWeb_Netsuite_Helper_Queue::NETSUITE_IMPORT_QUEUE);
        $this->processQueue($logger);
        $this->populateDeleteQueue();
        $this->processDeleteQueue();
    }

    public function processQueue($logger = null) {
        if (!Mage::helper('rocketweb_netsuite')->isEnabled()) {
            return;
        }
        
        $maxMessages = (int) Mage::getStoreConfig('rocketweb_netsuite/queue_processing/import_batch_size');
        
        if (!$maxMessages) {
            $maxMessages = 50;
        }

        $queue = Mage::helper('rocketweb_netsuite/queue')->getQueue(RocketWeb_Netsuite_Helper_Queue::NETSUITE_IMPORT_QUEUE);
        $messages = $queue->receive($maxMessages);

        foreach ($messages as $originalMessage) {
            $queueData = array (
                'message_id' => $originalMessage->__get('message_id'),
                'queue_id' => $originalMessage->__get('queue_id'),
                'handle' => $originalMessage->__get('handle'),
                'body' => $originalMessage->body,
                'timeout' => $originalMessage->__get('timeout'),
                'created' => $originalMessage->__get('created'),
                'priority' => $originalMessage->__get('priority')
            );
            $message = Mage::getModel('rocketweb_netsuite/queue_message')->unpack($originalMessage->body, RocketWeb_Netsuite_Helper_Queue::NETSUITE_IMPORT_QUEUE);
            $processModelString = 'rocketweb_netsuite/process_import_' . $message->getAction();
            $processModel = Mage::getModel($processModelString);
            
            if (!get_class($processModel)) {
                Mage::helper('rocketweb_netsuite')->log("Action {$message->getAction()} requires model $processModelString");
                continue;
            }
            else {
                try {
                    $processModel->process($message->getObject(), $queueData);
                    $queue->deleteMessage($originalMessage);
                }
                catch (Exception $ex) {
        		    if ($logger) {
        		         $logger->logProgress($ex->getMessage());
        		    }
                    Mage::helper('rocketweb_netsuite')->log($ex->getMessage());
                }
            }
        }
    }

    public function populateDeleteQueue() {
        if (!Mage::helper('rocketweb_netsuite')->isEnabled()) {
            return;
        }

        $time = Mage::helper('rocketweb_netsuite')->getServerTime();
        $updatedFrom = $this->getUpdatedFromDateInNetsuiteFormat(RocketWeb_Netsuite_Helper_Queue::NETSUITE_DELETE_QUEUE);
        $importableEntities = $this->getImportableEntities();
        
        foreach ($importableEntities as $path => $name) {
            $importableEntityModel = Mage::getModel('rocketweb_netsuite/process_import_' . $path);
            
            if (!$importableEntityModel) {
                Mage::helper('rocketweb_netsuite')->log("Model class not found for $path");
                continue;
            }
            
            if (!$importableEntityModel instanceof RocketWeb_Netsuite_Model_Process_Import_Abstract) {
                Mage::helper('rocketweb_netsuite')->log("$path is not an instance of RocketWeb_Netsuite_Model_Process_Import_Abstract");
                continue;
            }
            
            if (!$importableEntityModel->isActive()) {
                continue;
            }
            
            $records = $importableEntityModel->queryNetsuiteForDeletedRecords($updatedFrom);
            
            if ($records) {
                foreach ($records as $record) {
                    try{
                        $message = Mage::getModel('rocketweb_netsuite/queue_message');
                        $message = $message->create($importableEntityModel->getDeleteMessageType(), $record->record->internalId, RocketWeb_Netsuite_Helper_Queue::NETSUITE_DELETE_QUEUE,$record);
                    }catch(Exception $e){
                        Mage::helper('rocketweb_netsuite')->log($e->getMessage());
                    }

                    if (!$importableEntityModel->isQueued($message)) {
                        Mage::helper('rocketweb_netsuite/queue')->getQueue(RocketWeb_Netsuite_Helper_Queue::NETSUITE_DELETE_QUEUE)->send($message->pack());
                    }
                }
            }
        }

        Mage::helper('rocketweb_netsuite/queue')->setLastUpdateAccessDate($time,RocketWeb_Netsuite_Helper_Queue::NETSUITE_DELETE_QUEUE);
    }
    
    public function processDeleteQueue() {
        if (!Mage::helper('rocketweb_netsuite')->isEnabled()) {
            return;
        }

        $maxMessages = (int) Mage::getStoreConfig('rocketweb_netsuite/queue_processing/import_batch_size');
        
        if (!$maxMessages) {
            $maxMessages = 50;
        }

        $queue = Mage::helper('rocketweb_netsuite/queue')->getQueue(RocketWeb_Netsuite_Helper_Queue::NETSUITE_DELETE_QUEUE);
        $messages = $queue->receive($maxMessages);

        foreach ($messages as $originalMessage) {
            $message = Mage::getModel('rocketweb_netsuite/queue_message')->unpack($originalMessage->body,RocketWeb_Netsuite_Helper_Queue::NETSUITE_DELETE_QUEUE);
            $processModelString = 'rocketweb_netsuite/process_delete_'.preg_replace('/_delete$/','',$message->getAction());
            $processModel = Mage::getModel($processModelString);
            
            if (!get_class($processModel)) {
                Mage::helper('rocketweb_netsuite')->log("Action {$message->getAction()} requires model $processModelString");
                continue;
            }
            else {
                try {
                    $processModel->processDeleteOperation($message->getObject());
                    $queue->deleteMessage($originalMessage);
                }
                catch (Exception $ex) {
                    Mage::helper('rocketweb_netsuite')->log($ex->getMessage());
                }
            }
        }

    }

    protected function getUpdatedFromDateInNetsuiteFormat($queueType) {
        $lastUpdateAccessDate = Mage::helper('rocketweb_netsuite/queue')->getLastUpdateAccessDate($queueType);
        
        if (!$lastUpdateAccessDate) {
            $lastUpdateAccessDate = null;
        }
        else {
            $lastUpdateAccessDate = new DateTime($lastUpdateAccessDate);
        }

        $updatedFromDefault = Mage::getStoreConfig('rocketweb_netsuite/queue_processing/updated_from_minutes');
        $updatedFromDefault = gmdate('Y-m-d H:i:s',mktime(date("H"),date("i")-$updatedFromDefault));
        $updatedFromDefault = new DateTime($updatedFromDefault);

        if (is_null($lastUpdateAccessDate)) {
            $retDate = $updatedFromDefault;
        }
        else {
            if ($lastUpdateAccessDate->getTimestamp() > $updatedFromDefault->getTimestamp()) {
                $retDate = $lastUpdateAccessDate;
            }
            else {
                $retDate = $updatedFromDefault;
            }
        }

        return $retDate->format(DateTime::ISO8601);
    }

    public function getImportableEntities() {
        $iniFile = Mage::getBaseDir().'/files/netsuite/netsuite.ini';
        if(file_exists($iniFile))
        {
            $nsConfig = parse_ini_file($iniFile, true);
            return $nsConfig['import_entities'];
        }else{
            return Mage::getConfig()->getNode('rocketweb_netsuite/import_entities')->asArray();
        }
    }
    
    private function test() {
        return false;
    }
}
