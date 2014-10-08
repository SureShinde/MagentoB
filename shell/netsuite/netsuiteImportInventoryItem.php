<?php

require_once dirname(__FILE__) . '/../abstract.php';

class Bilna_Netsuitesync_Shell_NetsuiteImportInventoryItem extends Mage_Shell_Abstract {

	const PROCESS_ID = 'cron_inventoryitem';
    protected $_lockFile = null;

    public function run() {
    	$this->_start();
                        
        // checking process locking file
        if ($this->_isLocked()) {
            $this->writeLog(sprintf("Another '%s' process is running! Abort", self::PROCESS_ID));
            $this->_end();
            exit;
        }

        $netsuiteService = Mage::helper('rocketweb_netsuite')->getNetsuiteService();

        $search = new CustomRecordSearchAdvanced();
        $search->savedSearchId = 222;

        $request = new SearchRequest();
        $request->searchRecord = $search;

        $searchResponse = $netsuiteService->search($request);

        if (!$searchResponse->searchResult->status->isSuccess) {
		    echo "SEARCH ERROR";
		} else {
			echo "SEARCH SUCCESS, records found: " . $searchResponse->searchResult->totalRecords . "<br>";
			$records = $searchResponse->searchResult->searchRowList->searchRow;
			$i = 1;
			$strName = '';
			$strInternalId = '';
			$strItem ='';
			$strQuantity ='';
			$strAmount ='';

			foreach($records as $record) {
				$strName = $record->basic->name[0]->searchValue;
				$strInternalId = $record->basic->id[0]->searchValue;
print_r($record);
				echo "No: " . $i . "<br>";		
	    		echo "Name: " . $strName . "<br>";
				echo "Internal Id: " . $strInternalId . "<br>";
			
				if(!is_null($record->basic->customFieldList)){
					foreach($record->basic->customFieldList->customField as $cField){

						print_r($cField);
						/*if($cField->scriptId  == 'custrecord_joborderitem'){
							$strItem = $cField->searchValue->internalId;
						}

						if($cField->scriptId  == 'custrecord_joborderitemqty'){
							$strQuantity = $cField->searchValue;
						}

						if($cField->scriptId  == 'custrecord_joborderamount'){
							$strAmount = $cField->searchValue;
						}*/
					}
				}

	  	    	/*echo "Item: " . $strItem . "<br>";
		    	echo "Quantity: " . $strQuantity . "<br>";
		    	echo "Amount: " . $strAmount . "<br>";*/
				$i++;
			}
		}

    }

    protected function _start() {
        $this->writeLog("Start import inventory item ...");
    }
    
    protected function _end() {
        $this->writeLog("End import inventory item ...");
    }

    protected function writeLog($message) {
        Mage::log($message, null, "netsuite_import_inventory_item.log");
    }
    
    protected function _lock() {
        $handle = fopen($this->_lockFile, 'w');
        $content = date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(time()));
        
        fwrite($handle, $content);
        fclose($handle);
    }
    
    protected function _unlock() {
        if (file_exists($this->_lockFile)) {
            unlink($this->_lockFile);
            
            return true;
        }
        
        return false;
    }

    protected function _isLocked() {
        if ($this->_lockFile == null) {
            $this->_lockFile = $this->_getLockFile();
        }
        
        if (file_exists($this->_lockFile)) {
            return true;
        }
        
        //create lock file
        $this->_lock();
        
        return false;
    }
    
    protected function _getLockFile() {
        $varDir = Mage::getConfig()->getVarDir('locks');
        $this->_lockFile = $varDir . DS . self::PROCESS_ID . '.lock';
        
        return $this->_lockFile;
    }
}

$shell = new Bilna_Netsuitesync_Shell_NetsuiteImportInventoryItem();
$shell->run();