<?php
/**
 * Description of Bilna_Worker_Order_CreateInvoicesCcManually
 *
 * @path    worker/order/CreateInvoicesCcManually.php
 * @author  Bilna Development Team <development@bilna.com>
 */

require_once dirname(__FILE__) . '/Order.php';

class Bilna_Worker_Order_CreateInvoicesCcManually extends Bilna_Worker_Order_Order {
    protected $_salesOrderTable = 'sales_flat_order';
    protected $_salesOrderPaymentTable = 'sales_flat_order_payment';
    protected $_paymentMethodHelper;
    protected $_paymentMethodCc = [];
    protected $_paymentMethodVtdirect = [];
    protected $_paymentMethodVa = [];
    protected $_tubeAllow = 'invoice';

    protected function _process() {
        try {
            $queryOrders = $this->_getQuery();
                
            while ($order = $queryOrders->fetch()) {
                $this->_logProgress($order['entity_id'] . " | " . $order['increment_id'] . " | " . $order['method']);
                continue;
                $orderId = $order['entity_id'];
                $orderIncrementId = $order['increment_id'];
                $orderStatusVtdirect = $this->_getOrderStatusVtdirect($orderIncrementId);
                $this->_logProgress("{$orderIncrementId}: {$this->_prepareData($orderStatusVtdirect)}");
                
                //$this->_logProgress("#{$orderIncrementId} store to queue.");

//                if ($this->_queuePut($orderId)) {
//                    $this->_logProgress("{$orderId} store to queue.");
//                }
            }
        }
        catch (Exception $e) {
            Mage::logException($e);
            $this->_logProgress($e->getMessage());
        }
    }
    
    protected function _getQuery() {
//        $status = 'pending';
        $status = Bilna_Checkout_Model_Api2_Resource::ORDER_STATUS_PENDING_INVOICE;
        $state = Mage_Sales_Model_Order::STATE_NEW;
        $paymentMethodCc = $this->_getPaymentMethodCc();
        $paymentMethodVtdirect = $this->_getPaymentMethodVtdirect();
        $paymentMethodVa = $this->_getPaymentMethodVa();
        $methodAllow = implode("','", array_merge($paymentMethodCc, $paymentMethodVtdirect, $paymentMethodVa));
        
        $sql = "SELECT `sfo`.`entity_id`, `sfo`.`increment_id`, `sfop`.`method` ";
        $sql .= "FROM `{$this->_salesOrderTable}` AS `sfo` ";
        $sql .= "LEFT JOIN `{$this->_salesOrderPaymentTable}` AS `sfop` ON `sfo`.`entity_id` = `sfop`.`parent_id` ";
        $sql .= "WHERE `sfo`.`status` = '{$status}' AND `sfo`.`state` = '{$state}' AND `sfop`.`method` IN ('{$methodAllow}') ";
        //$sql .= "AND `sfo`.`entity_id` = 153893 ";
        $sql .= "ORDER BY `sfo`.`entity_id` ";
        $sql .= "LIMIT 5 ";
        $this->_critical($sql);
            
        return $this->_dbRead->query($sql);
    }
    
    protected function _getOrderStatusVtdirect($orderIncrementId) {
        Mage::helper('paymethod')->loadVeritransNamespace();

        //- setting config vtdirect
        $paymethodApiModel = Mage::getModel('paymethod/api');
        $vtdirectConfig = $paymethodApiModel->getVtdirectConfig();
        Veritrans_Config::$serverKey = $paymethodApiModel->getVtdirectServerKey($vtdirectConfig);
        Veritrans_Config::$isProduction = $paymethodApiModel->getVtdirectIsProduction($vtdirectConfig);
        
        return Veritrans_Transaction::status($orderIncrementId);
    }
    
    protected function _getPaymentMethodHelper() {
        if (!$this->_paymentMethodHelper) {
            $this->_paymentMethodHelper = Mage::helper('paymethod');
        }
        
        return $this->_paymentMethodHelper;
    }


    protected function _getPaymentMethodCc() {
        if (!$this->_paymentMethodCc) {
            $this->_paymentMethodCc = $this->_getPaymentMethodHelper()->getPaymentMethodCc();
        }
        
        return $this->_paymentMethodCc;
    }
    
    protected function _getPaymentMethodVtdirect() {
        if (!$this->_paymentMethodVtdirect) {
            $this->_paymentMethodVtdirect = $this->_getPaymentMethodHelper()->getPaymentMethodVtdirect();
        }
        
        return $this->_paymentMethodVtdirect;
    }
    
    protected function _getPaymentMethodVa() {
        if (!$this->_paymentMethodVa) {
            $this->_paymentMethodVa = $this->_getPaymentMethodHelper()->getPaymentMethodVA();
        }
        
        return $this->_paymentMethodVa;
    }

    protected function _storeChargeDataToQueue($charge, $invoice = true) {
        try {
            $hostname = Mage::getStoreConfig('bilna_queue/beanstalkd_settings/hostname');
            $pheanstalk = new Pheanstalk($hostname);
            $pheanstalk->useTube('vt_charge')->put(json_encode($charge)); //- store charge request-response for API Charging Info

            if ($invoice) {
                $pheanstalk->useTube('invoice')->put(json_encode($charge['response']), '', 60); //- store charge reseponse for create invoice or cancel order (delay 1 minute)
            }
        }
        catch (Exception $e) {
            Mage::logException($e);
        }
    }
}

$worker = new Bilna_Worker_Order_CreateInvoicesCcManually();
$worker->run();
