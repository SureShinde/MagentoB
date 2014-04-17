<?php
/**
 * Description of Bilna_Netsuitesync_Shell_NetsuiteExportOrder
 *
 * @author Bilna Development Team <development@bilna.com>
 */

require_once dirname(__FILE__) . '/../abstract.php';

class Bilna_Netsuitesync_Shell_NetsuiteExportOrder extends Mage_Shell_Abstract {
    public function run() {
        $this->writeLog("Started export orders ...");
        
        $orderCollection = $this->getOrderCollection();
        //$orderCollection->printLogQuery(true);
        
        $this->writeLog("Processing {$orderCollection->getSize()} orders ...");
        
        foreach ($orderCollection as $order) {
            $netsuiteCustomerId = Mage::helper('rocketweb_netsuite/mapper_customer')->createNetsuiteCustomerFromOrder($order);
            $this->writeLog("NetsuiteCustomerId {$netsuiteCustomerId}");
        }
        
        $this->writeLog("Ended export orders ...");
    }
    
    protected function getOrderCollection() {
        return Mage::getModel('sales/order')->getCollection();
    }
    
    protected function writeLog($message) {
        @error_log($message . "\n", 3, "/tmp/netsuiteExportOrder.log");
    }
    
}

$shell = new Bilna_Netsuitesync_Shell_NetsuiteExportOrder();
$shell->run();