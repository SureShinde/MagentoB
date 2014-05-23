<?php
/**
 * Description of Bilna_Paymethod_Model_Observer_Paymentmapping
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Paymethod_Model_Observer_Paymentmapping {
    private $paymentMappingAllow = array ('veritrans', 'anzcc', 'scbcc', 'bnicc', 'visa');
    
    public function process() {
        $this->writeLog("Paymentmapping start..");
        $orderPaymentCollection = $this->getOrderPaymentCollection();
        $this->writeLog(sprintf("Total order mapping: %d\n", count($orderPaymentCollection)));
        
        if (is_array($orderPaymentCollection) && count($orderPaymentCollection)) {
            $successOrder = 0;
            $successQuote = 0;
            $failedOrder = 0;
            $failedQuote = 0;
            
            foreach ($orderPaymentCollection as $orderPayment) {
                /**
                 * update table sales_flat_order_payment
                 */
                if ($this->updateSalesFlatOrderPayment($orderPayment)) {
                    $salesFlatOrderPaymentStatus = "success";
                    $successOrder++;
                }
                else {
                    $salesFlatOrderPaymentStatus = "failed";
                    $failedOrder++;
                }
                
                /**
                 * update table sales_flat_quote_payment
                 */
                if ($this->updateSalesFlatQuotePayment($orderPayment)) {
                    $salesFlatQuotePaymentStatus = "success";
                    $successQuote++;
                }
                else {
                    $salesFlatQuotePaymentStatus = "failed";
                    $failedQuote++;
                }
                
                $this->writeLog(sprintf("%s: sales_flat_order_payment update from %s to %s [%s]", $orderPayment['increment_id'], $orderPayment['method'], $orderPayment['new_method'], $salesFlatOrderPaymentStatus));
                $this->writeLog(sprintf("%s: sales_flat_quote_payment update from %s to %s [%s]", $orderPayment['quote_id'], $orderPayment['method'], $orderPayment['new_method'], $salesFlatQuotePaymentStatus));
            }
        }
        
        
        $this->writeLog(sprintf("\nTotal update order success: %d", $successOrder));
        $this->writeLog(sprintf("Total update order failed: %d", $failedOrder));
        $this->writeLog(sprintf("Total update quote success: %d", $successQuote));
        $this->writeLog(sprintf("Total update quote failed: %d", $failedQuote));
        $this->writeLog('Paymentmapping end..');
        exit;
    }
    
    private function getOrderPaymentCollection() {
        $orderCollection = $this->getOrderCollection();
        $binCollection = $this->getBinCollection();
        $orderData = array ();
        
        foreach ($orderCollection as $order) {
            $orderData[] = array (
                'order_id' => $order->getId(),
                'increment_id' => $order->getIncrementId(),
                'quote_id' => $order->getQuoteId(),
                'method' => $order->getMethod(),
                'cc_bins' => $order->getCcBins(),
                'new_method' => $this->mappingNewMethod($order->getCcBins(), $binCollection)
            );
        }
        
        return $orderData;
    }
    
    private function getOrderCollection() {
        $paymentAllow = implode("','", $this->paymentMappingAllow);
        $orderCollection = Mage::getModel('sales/order')->getCollection();
        //$orderCollection->addFieldToFilter('status', 'pending');
        $orderCollection->getSelect()->join(
            array ('SFOP' => 'sales_flat_order_payment'),
            "SFOP.method IN ('" . $paymentAllow . "') AND main_table.entity_id = SFOP.parent_id",
            array ('method', 'cc_bins'),
            null,
            'left'
        );
        
        //$orderCollection->printLogQuery(true);
        //exit;
        
        return $orderCollection;
    }
    
    private function getBinCollection() {
        $read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $sql = "SELECT code, issuer ";
        $sql .= "FROM bin_code ";
        $query = $read->query($sql);
        
        return $query->fetchAll();
    }
    
    private function mappingNewMethod($ccBins, $binCollection) {
        $result = 'othervisa';
        
        if (is_array($binCollection) && count($binCollection > 0)) {
            foreach ($binCollection as $bin) {
                if ($ccBins == $bin['code']) {
                    $result = $bin['issuer'];
                }
            }
        }
        
        return $result;
    }
    
    private function updateSalesFlatOrderPayment($orderPayment) {
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        $sql = sprintf("UPDATE sales_flat_order_payment SET method = '%s' WHERE parent_id = %d LIMIT 1 ", $orderPayment['new_method'], $orderPayment['order_id']);
        $query = $write->query($sql);
        
        if ($query) {
            return true;
        }
        
        return false;
    }
    
    private function updateSalesFlatQuotePayment($orderPayment) {
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        $sql = sprintf("UPDATE sales_flat_quote_payment SET method = '%s' WHERE quote_id = %d LIMIT 1 ", $orderPayment['new_method'], $orderPayment['quote_id']);
        $query = $write->query($sql);
        
        if ($query) {
            return true;
        }
        
        return false;
    }
    
    private function addStatusHistory($incrementId, $message) {
        $order = Mage::getModel('sales/order')->loadByIncrementId($incrementId);
        $order->addStatusHistoryComment($message);
        $order->save();
    }
    
    private function writeLog($message) {
        @error_log(sprintf("%s\n", $message), 3, sprintf("/tmp/paymentmapping.%s.log", date('Ymd')));
    }
}
