<?php
/**
 * Description of Bilna_Netsuitesync_Shell_NetsuiteExportProductCost
 *
 * @author Bilna Development Team <development@bilna.com>
 */

require_once dirname(__FILE__) . '/../abstract.php';

class Bilna_Netsuitesync_Shell_NetsuiteExportProductCost extends Mage_Shell_Abstract {
    public function run() {
        $this->writeLog("Start export product cost ...");
        $productCostCollection = $this->getProductCostCollection();
        $this->writeLog("Processing {$productCostCollection->getSize()} products ...");
        
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
                    $this->deleteProductCostQueue($product->getId());
                }
                else {
                    $this->writeLog("failed export product cost #" . $product->getProductId());
                }
            }
            else {
                $this->writeLog("Cannot load netsuite_internal_id from product #" . $product->getProductId());
            }
        }
        
        $this->writeLog("End export product cost ...");
    }
    
    protected function getProductCostCollection() {
        $productCostCollection = Mage::getModel('rocketweb_netsuite/productcost')->getCollection();

        return $productCostCollection;
    }
    
    protected function deleteProductCostQueue($id) {
        $model = Mage::getModel('rocketweb_netsuite/productcost');

        if ($model->setId($id)->delete()) {
            $this->writeLog("delete queueProductSaveCostNetsuite #{$id} successfully");
            return true;
        }
        else {
            $this->writeLog("delete queueProductSaveCostNetsuite #{$id} failed");
            return false;
        }
    }

    protected function writeLog($message) {
        Mage::log($message, null, "netsuite_product_cost.log");
    }
}

$shell = new Bilna_Netsuitesync_Shell_NetsuiteExportProductCost();
$shell->run();
