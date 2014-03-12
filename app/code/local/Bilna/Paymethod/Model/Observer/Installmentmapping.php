<?php
/**
 * Description of Bilna_Paymethod_Model_Observer_Installmentmapping
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Paymethod_Model_Observer_Installmentmapping {
    private $paymentMappingAllow = array ('anzcc', 'scbcc', 'vtdirect','anzvisa','anzmc','bukopinvisa','bukopinmc','bcavisa','bcamc','danamonvisa','danamonmc','biivisa','biimc','mandirivisa','mandirimc','megavisa','megamc','bnivisa','bnimc','permatavisa','permatamc','brivisa','brimc','cimbvisa','cimbmc','citibankvisa','citibankmc','hsbcvisa','hsbcmc','scvisa','scmc','paninplatinumvisa','paningoldvisa','othervisa','othermc');
    
    public function process() {
        $this->writeLog("Installmentmapping start..");
        $orderPaymentCollection = $this->getOrderPaymentCollection();
        $this->writeLog(sprintf("Total order mapping: %d\n", count($orderPaymentCollection)));
        
        if (is_array($orderPaymentCollection) && count($orderPaymentCollection)) {
            $updateInstallmentStatus = '';
            $successUpdateInstallmentCount = 0;
            $failedUpdateInstallmentCount = 0;
            
            foreach ($orderPaymentCollection as $orderPayment) {
                $orderId = $orderPayment['order_id'];
                $incrementId = 0;
                $method = $orderPayment['method'];
                $orderItems = $orderPayment['items'];
                $installmentCollection = unserialize(Mage::getStoreConfig('payment/' . $method . '/installment'));
                
                foreach ($orderItems as $items) {
                    $itemId = $items['item_id'];
                    $installmentType = $items['installment_type'];
                    $tenor = $this->getInstallmentTenor($installmentCollection, $installmentType);
                    $mappingInstallment = $this->setMappingInstallment($orderId, $itemId, $tenor);
                    
                    if ($mappingInstallment) {
                        $updateInstallmentStatus = 'success';
                        $successUpdateInstallmentCount++;
                    }
                    else {
                        $updateInstallmentStatus = 'failed';
                        $failedUpdateInstallmentCount++;
                    }
                    
                    $this->writeLog(sprintf("%d (%d): sales_flat_order_item update status => %s", $orderId, $itemId, $updateInstallmentStatus));
                }
            }
        }
        
        $this->writeLog(sprintf("\nTotal update orderItem success: %d", $successUpdateInstallmentCount));
        $this->writeLog(sprintf("Total update orderItem failed: %d", $failedUpdateInstallmentCount));
        $this->writeLog('Installmentmapping end..');
        exit;
    }
    
    private function getOrderPaymentCollection() {
        $orderCollection = $this->getOrderCollection();
        $orderData = array ();
        
        foreach ($orderCollection as $order) {
            $orderData[] = array (
                'order_id' => $order->getId(),
                'increment_id' => $order->getIncrementId(),
                'method' => $order->getMethod(),
                'items' => $this->getOrderItem($order->getId())
            );
        }
        
        return $orderData;
    }
    
    private function getOrderCollection() {
        $paymentAllow = implode("','", $this->paymentMappingAllow);
        $orderCollection = Mage::getModel('sales/order')->getCollection();
        $orderCollection->getSelect()->join(
            array ('SFOP' => 'sales_flat_order_payment'),
            "SFOP.method IN ('" . $paymentAllow . "') AND main_table.entity_id = SFOP.parent_id",
            array ('method', 'cc_bins'),
            null,
            'left'
        );
        $orderCollection->getSelect()->limit(5000);
        //$orderCollection->printLogQuery(true);
        //exit;
        
        return $orderCollection;
    }
    
    private function getOrderItem($orderId) {
        $read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $sql = sprintf("SELECT item_id, installment_type FROM sales_flat_order_item WHERE order_id = %d ", $orderId);
        $query = $read->query($sql);
        
        return $query->fetchAll();
    }
    
    private function setMappingInstallment($orderId, $itemId, $tenor) {
        $installment = 0;
        $installmentType = null;
        $installmentMethod = null;
        
        if ($tenor > 1) {
            $installment = 1;
            $installmentType = $tenor;
            $installmentMethod = 'manual';
        }
        
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        $sql = sprintf("UPDATE sales_flat_order_item SET installment = %d, installment_type = %d, installment_method = '%s' ", $installment, $installmentType, $installmentMethod);
        $sql .= sprintf("WHERE item_id = %d AND order_id = %d LIMIT 1 ", $itemId, $orderId);
        $query = $write->query($sql);
        //$this->writeLog($sql);
        
        if ($query) {
            return true;
        }
        
        return false;
    }
    
    private function getInstallmentTenor($installmentCollection, $installmentType) {
        $result = 0;
        
        if (is_array($installmentCollection) && count($installmentCollection) > 0) {
            foreach ($installmentCollection as $installment) {
                if ($installment['id'] == $installmentType) {
                    $result = $installment['tenor'];
                    break;
                }
            }
        }
        
        return $result;
    }
    
    private function writeLog($message) {
        @error_log(sprintf("%s\n", $message), 3, sprintf("/tmp/installmentmapping.%s.log", date('Ymd')));
    }
}
