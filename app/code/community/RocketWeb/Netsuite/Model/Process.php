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
    protected $_exceptedImportableEntities = array('creditmemo');
	
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

        // if there is no importable entities to be processed, just exit the cron process
        if (count($importableEntities) == 0)
            die("There is no importable entities to be processed\n");

        // register the obtained importable entities into the registry to be used in the db receiver
        if (Mage::registry('current_importable_entities')) {
            Mage::unregister('current_importable_entities');
        }
        Mage::register('current_importable_entities',$importableEntities);
        
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
                                $internalRecordIds[] = $record->internalId;
                            }
                        }
                        
                        if (is_array($internalRecordIds)) {
                            //we need to get the full object for each record, as item lists are missing from search requests
                            foreach ($internalRecordIds as $internalRecordId) {
                                $request = new GetRequest();
                                $request->baseRef = new RecordRef();
                                $request->baseRef->internalId = $internalRecordId;
                                $request->baseRef->type = $importableEntityModel->getRecordType();
                                
                                $logger->logProgress("request date " . date("Y-m-d H:i:s"));
                                $logger->logProgress("request {$name}: " . json_encode($request));
                                
                                /*$getResponse = Mage::helper('rocketweb_netsuite')->getNetsuiteService()->get($request);
                                
                                $logger->logProgress("response date " . date("Y-m-d H:i:s"));
                                $logger->logProgress("response {$name}: " . json_encode($getResponse));
                                $logger->logProgress("----------------------------------------------------------------------------------------");
                                
                                if (!$getResponse->readResponse->status->isSuccess) {
                                    throw new Exception((string) print_r($getResponse->readResponse->status->statusDetail, true));
                                }
                                else {
                                    $record = $getResponse->readResponse->record;
                                    $message = Mage::getModel('rocketweb_netsuite/queue_message');
                                    $message = $message->create($importableEntityModel->getMessageType(), $record->internalId, RocketWeb_Netsuite_Helper_Queue::NETSUITE_IMPORT_QUEUE, $record);
                                    
                                    if (!$importableEntityModel->isQueued($message)) {
                                        Mage::helper('rocketweb_netsuite/queue')->getQueue(RocketWeb_Netsuite_Helper_Queue::NETSUITE_IMPORT_QUEUE)->send($message->pack(), Mage::helper('rocketweb_netsuite')->getRecordPriority($path));
                                    }
                                }*/

                                $lastModifiedDate = $updatedFrom;
                                $x=0;
                                $tryNumber = 10;
                                do{
                                    ++$x;
                                    $getResponse = Mage::helper('rocketweb_netsuite')->getNetsuiteService()->get($request);

                                    $logger->logProgress("response date " . date("Y-m-d H:i:s"));
                                    $logger->logProgress("response {$name}: " . json_encode($getResponse));
                                    $logger->logProgress("----------------------------------------------------------------------------------------");

                                    $error = true;
                                    if (!$getResponse->readResponse->status->isSuccess) {
                                    //if (true) {
                                        //throw new Exception((string) print_r($getResponse->readResponse->status->statusDetail, true));
                                        $error = false;
                                        if($x == $tryNumber)
                                        {
                                            throw new Exception((string) print_r($getResponse->readResponse->status->statusDetail, true));
                                        }
                                        sleep(1);
                                    }
                                    else {
                                        $record = $getResponse->readResponse->record;
                                        $message = Mage::getModel('rocketweb_netsuite/queue_message');
                                        $message = $message->create($importableEntityModel->getMessageType(), $record->internalId, RocketWeb_Netsuite_Helper_Queue::NETSUITE_IMPORT_QUEUE, $record);
                                        
                                        if (!$importableEntityModel->isQueued($message)) {
                                            Mage::helper('rocketweb_netsuite/queue')->getQueue(RocketWeb_Netsuite_Helper_Queue::NETSUITE_IMPORT_QUEUE)->send($message->pack(), Mage::helper('rocketweb_netsuite')->getRecordPriority($path));
                                        }
                                    }   
                                }while($error == false && $x <= $tryNumber);


                            }
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
        $finalImportableEntities = array();

        // get available importable entities from config
        $availableEntities = Mage::getConfig()->getNode('rocketweb_netsuite/import_entities')->asArray();
        // get entities from Import Connection Settings for Record Type
        $recordtypes = Mage::getStoreConfig('rocketweb_netsuite/connection_import_recordtype/recordtype');
        $arr_recordtypes = explode(',', $recordtypes);

        // if recordtype argument is available
        if (Mage::registry('current_run_recordtype'))
        {
            $current_run_recordtype = Mage::registry('current_run_recordtype');

            // if recordtypes are available from Import Connection Settings for Record Type
            if (count($arr_recordtypes) > 0)
            {
                // if recordtype argument is all
                if ($current_run_recordtype == 'all') 
                {
                    foreach($arr_recordtypes as $type)
                        $finalImportableEntities[$type] = $availableEntities[$type];
                }
                else
                {
                    // check if the recordtype is available
                    if (isset($availableEntities[$current_run_recordtype]))
                    {
                        if (in_array($current_run_recordtype, $arr_recordtypes))
                            $finalImportableEntities[$current_run_recordtype] = $availableEntities[$current_run_recordtype];
                    }
                }
            }
        }
        else
        {
            // set the entities that are not in the entities inside the Import Connection Settings
            // for Record Type
            foreach($availableEntities as $path => $name)
            {
                if (!in_array($path, $arr_recordtypes))
                    $finalImportableEntities[$path] = $name;
            }
        }

        // if there is an entity that is not importable, we have to remove it
        foreach($this->_exceptedImportableEntities as $entity)
            unset($finalImportableEntities[$entity]);
        
        return $finalImportableEntities;
    }
    
    private function test() {
        return false;
    }
}
