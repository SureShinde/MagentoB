<?php
/**
 * Description of Bilna_Netsuitesync_Shell_NetsuiteExportProductCost
 *
 * @author Bilna Development Team <development@bilna.com>
 */

require_once dirname(__FILE__) . '/../abstract.php';

class Bilna_Netsuitesync_Shell_NetsuiteExportProductCost extends Mage_Shell_Abstract {
    const PROCESS_ID = 'cron_productcost';
    protected $_lockFile = null;
    //protected $_isLocked = null;

    public function run() {
        $this->_start();
                        
        // checking process locking file
        if ($this->_isLocked()) {
            $this->writeLog(sprintf("Another '%s' process is running! Abort", self::PROCESS_ID));
            $this->_end();
            exit;
        }
        
        $productCostCollection = $this->getProductCostCollection();
        $this->writeLog("Processing {$productCostCollection->getSize()} product cost ...");
        
        foreach ($productCostCollection as $product) {
            if ($product->getProductId()) {
                $netsuiteService = Mage::helper('rocketweb_netsuite')->getNetsuiteService();
                $netsuiteProductCost = Mage::helper('rocketweb_netsuite/mapper_productcost')->getNetsuiteFormat($product);
                
                $request = new UpdateRequest();
                $request->record = $netsuiteProductCost;
                $response = $netsuiteService->update($request);
                
                $this->writeLog("requestProductCost: " . json_encode($request));
                $this->writeLog("responseProductCost: " . json_encode($response));
                
                if ($response->writeResponse->status->isSuccess) {
                    // delete product cost queue
                    if ($deleteProductCostQueue = Mage::helper('rocketweb_netsuite/mapper_productcost')->deleteProductCostQueue($product)) {
                        $this->writeLog("delete queueProductSaveCostNetsuite #{$product->getId()} successfully");
                    }
                }
                else {
                    $this->writeLog("failed export product cost #" . $product->getProductId());
                }
            }
            else {
                $this->writeLog("Cannot load netsuite_internal_id from product #" . $product->getProductId());
            }
        }
        
        // Remove the lock.
        $this->_unlock();
        $this->_end();
    }
    
    protected function _start() {
        $this->writeLog("Start export product cost ...");
    }
    
    protected function _end() {
        $this->writeLog("End export product cost ...");
    }

    protected function getProductCostCollection() {
        $productCostCollection = Mage::getModel('rocketweb_netsuite/productcost')->getCollection();

        return $productCostCollection;
    }

    protected function writeLog($message) {
        Mage::log($message, null, "netsuite_product_cost.log");
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

$shell = new Bilna_Netsuitesync_Shell_NetsuiteExportProductCost();
$shell->run();
