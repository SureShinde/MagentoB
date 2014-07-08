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
        $this->writeLog("Processing {$orderCollection->getSize()} orders ...");
        
        foreach ($orderCollection as $order) {
            if (!$order || !$order->getEntityId()) {
                $this->writeLog("Cannot load order with id #{$order->getEntityId()} from Magento!");
                throw new Exception("Cannot load order with id #{$order->getEntityId()} from Magento!");
            }
            
            $magentoOrder = Mage::getModel('sales/order')->load($order->getEntityId());
            $netsuiteService = Mage::helper('rocketweb_netsuite')->getNetsuiteService();
            $netsuiteOrder = Mage::helper('rocketweb_netsuite/mapper_order')->getNetsuiteFormat($magentoOrder);

            //Mage::dispatchEvent('netsuite_new_order_send_before', array ('magento_order' => $magentoOrder, 'netsuite_order' => $netsuiteOrder));
            
            $request = new AddRequest();
            $request->record = $netsuiteOrder;
            $response = $netsuiteService->add($request);
            
            $this->writeLog("requestOrder: " . json_encode($request));
            $this->writeLog("responseOrder: " . json_encode($response));

            if ($response->writeResponse->status->isSuccess) {
                $netsuiteId = $response->writeResponse->baseRef->internalId;
                $magentoOrder->setNetsuiteInternalId($netsuiteId);
                $magentoOrder->getResource()->save($magentoOrder);
            }
            else {
                $this->writeLog(json_encode($response->writeResponse->status->statusDetail));
                throw new Exception(json_encode($response->writeResponse->status->statusDetail));
            }
        }
        
        $this->writeLog("Ended export orders ...");
    }
    
    protected function getOrderCollection() {
        $orderCollection = Mage::getModel('sales/order')->getCollection();
        $orderCollection->addAttributeToFilter('netsuite_internal_id', array ('eq' => ''));
        $orderCollection->addAttributeToFilter('entity_id', array ('lteq' => $this->getMaxOrderId()));
        $orderCollection->addAttributeToSort('entity_id', 'DESC');
        $orderCollection->getSelect()->limit($this->getOrderCollectionLimit());

        return $orderCollection;
    }
    
    protected function getMaxOrderId() {
        return Mage::getStoreConfig('rocketweb_netsuite/exports/max_order_entity_id');
    }

    protected function getOrderCollectionLimit() {
        return (int) Mage::getStoreConfig('rocketweb_netsuite/exports/limit');
    }

    protected function writeLog($message) {
        $filename = "netsuite.order." . date('Ymd', Mage::getModel('core/date')->timestamp(time())) . ".log";
        $datetime = date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(time()));
        @error_log("{$datetime} | {$message}\n", 3, "/usr/share/nginx/html/bilna.2014.new/var/log/netsuite/order/{$filename}");
    }
}

$shell = new Bilna_Netsuitesync_Shell_NetsuiteExportOrder();
$shell->run();
