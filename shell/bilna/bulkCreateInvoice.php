<?php
/**
 * Description of bulkCreateInvoice
 *
 * @path    shell/bilna/bulkCreateInvoice.php
 * @author  Bilna Development Team <development@bilna.com>
 */

require_once dirname(__FILE__) . '/../abstract.php';

class bulkCreateInvoice extends Mage_Shell_Abstract {
    protected $resource;
    protected $write;
    protected $read;
    protected $state = 'processing';
    protected $status = 'processing';
    protected $orderIncrementIds;

    const PROCESS_ID = 'cron_bulkCreateInvoice';
    protected $_lockFile = null;

    public function init() {
        $this->resource = Mage::getSingleton('core/resource');
        $this->write = $this->resource->getConnection('core_write');
        $this->read = $this->resource->getConnection('core_read');
    }

    public function run() {
        if ($this->_isLocked()) {
            $this->writeLog(sprintf("Another '%s' process is running! Abort", self::PROCESS_ID));
            exit;
        }
        $this->init();
        $orderIncrementIds = $this->getOrderIncrementIds();
        if ($this->getArg('check_veritrans') == 'true') {
            if ((is_array($orderIncrementIds)) && (count($orderIncrementIds) > 0)) {
                $mergedOrderIncrementIds = "'".implode("','", $orderIncrementIds)."'";
                $additionalQuery = " AND sfo.increment_id IN (".$mergedOrderIncrementIds.")";
            } else {
                $dateStart = (!$this->getArg('dateStart')) ? false : $this->getArg('dateStart');
                $dateEnd = (!$this->getArg('dateEnd')) ? false : $this->getArg('dateEnd');
                if (!$dateStart && !$dateEnd) {
                    $additionalQuery = " AND sfo.created_at BETWEEN DATE_FORMAT(NOW() - INTERVAL 7 DAY, '%Y-%m-%d 00:00:00') AND DATE_FORMAT(NOW(), '%Y-%m-%d %k:%i:%s')";
                } elseif ($dateStart && $dateEnd) {
                    $additionalQuery = " AND sfo.created_at BETWEEN ".$dateStart." AND ".$dateEnd;
                } else {
                    $this->_unlock();
                    $this->critical('Both of date start and date end must be filled or not setted');
                }
            }
            $paymentMethods = Mage::getStoreConfig('bilna_module/paymethod/payment_hide');
            $paymentMethods = str_replace(',mandiriecash', '', $paymentMethods);
            $paymentMethods = str_replace('mandiriecash,', '', $paymentMethods);
            $paymentMethods = str_replace(',', "','", $paymentMethods);
            $sql = "
                SELECT
                    sfo.increment_id
                FROM
                    sales_flat_order sfo,
                    sales_flat_order_payment sfop
                WHERE
                    NOT EXISTS (SELECT null FROM sales_flat_invoice sfi WHERE sfi.order_id = sfo.entity_id)
                    AND sfo.state = 'processing'
                    AND sfo.entity_id = sfop.parent_id
                    AND sfop.method IN ('".$paymentMethods."')".$additionalQuery;
            $orderIds = $this->read->fetchAll($sql);

            $orderIncrementIds = $this->getCCSuccessVeritransOrderIds($orderIds);
        }

        //- step 1 => get order where status & state is processing
        if (count($orderIncrementIds) == 0) {
            $this->_unlock();
            $this->critical('Order not found.');
        }

        //- step 2 => process order (normalisasi)
        $this->processOrders($orderIncrementIds);
        $this->logProgress('Process all order successfully.');
        $this->_unlock();
    }

    protected function getCCSuccessVeritransOrderIds($orderIncrementIds)
    {
        require_once dirname(__FILE__) . '/../../app/code/local/Bilna/Paymethod/lib/Veritrans.php';
        Veritrans_Config::$serverKey = Mage::getStoreConfig('payment/vtdirect/server_key');
        Veritrans_Config::$isProduction = (Mage::getStoreConfig('payment/vtdirect/development_testing') == 1) ? false : true;
        $successOrderNoWithoutInv = array();

        foreach ($orderIncrementIds as $orderIncrementId) {
            try {
                $result = (array) Veritrans_Transaction::status($orderIncrementId['increment_id']);
            }
            catch (Exception $e) {
                $this->logProgress('Veritrans check order ID '.$orderIncrementId['increment_id'].' status error with message : '.$e->getMessage(), true);
                continue;
            }

            if ((isset($result['transaction_status'])) && (($result['transaction_status'] == 'settlement') || ($result['transaction_status'] == 'success') || ($result['transaction_status'] == 'capture'))) {
                $successOrderNoWithoutInv[] = $orderIncrementId['increment_id'];
                $this->logProgress('Order : '.$orderIncrementId['increment_id'].' verified by veritrans with status : '.$result['transaction_status'], true);
            }
        }

        return $successOrderNoWithoutInv;
    }
    
    protected function getOrderIncrementIds() {
        if (!$this->getArg('orders')) {
            return array();
        }
        
        $orders = str_replace(' ', '', $this->getArg('orders'));
        $orderIncrementIds = explode(',', $orders);
        
        return $orderIncrementIds;
    }

    protected function processOrders($orderIncrementIds) {
        foreach ($orderIncrementIds as $orderIncrementId) {
            $order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);

            if (!$order->getId()) {
                $this->logProgress($orderIncrementId . ' => load order failed.');
                continue;
            }
            
            $orderId = $order->getId();
            $orderNetsuiteInternalId = $order->getNetsuiteInternalId();
            
            //- normalisasi order
            if (!$this->normalizeOrder($orderId)) {
                $this->logProgress($orderIncrementId . ' => Normalize order failed.');
                continue;
            }
            $this->logProgress($orderIncrementId . ' => Normalize order success.');
            
            //- normalisasi order item
            if (!$this->normalizeOrderItem($orderId)) {
                $this->logProgress($orderIncrementId . ' => Normalize order item failed.');
                continue;
            }
            $this->logProgress($orderIncrementId . ' => Normalize order item success.');

            //- normalisasi order payment
            if (!$this->normalizeOrderPayment($orderId)) {
                $this->logProgress($orderIncrementId . ' => Normalize order payment failed.');
                continue;
            }
            $this->logProgress($orderIncrementId . ' => Normalize order payment success.');
            
            //- create invoice
            $order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);
            $invoice = $this->createInvoice($orderId, $order);
            
            if (!$invoice) {
                $this->logProgress($orderIncrementId . ' => Invoice not found.');
                continue;
            }
            
            $invoiceId = $invoice->getId();
            $invoiceNetsuiteInternalId = $invoice->getNetsuiteInternalId();

            if ($this->getArg('check_veritrans') == 'true') {
                $this->logProgress('Invoice for Order : '.$orderIncrementId.' successfully created with Invoice ID : '.$invoiceId, true);
            }
            
            //- remove message invoice
            if (!$this->removeMessageInvoice($invoiceId)) {
                $this->logProgress($orderIncrementId . ' => Remove queue InvoiceSave failed.');
                $this->logProgress('Remove message invoice #' . $invoice->getId() . ' failed');
                continue;
            }
            $this->logProgress($orderIncrementId . ' => Remove queue InvoiceSave success.');
            
            //- insert ke table message dgn body sebagai invoice_save dan entity_id nya
            if (empty($invoiceNetsuiteInternalId) || is_null($invoiceNetsuiteInternalId)) {
                if (!$this->triggerNetsuiteInvoiceSave($orderId, $invoiceId)) {
                    $this->logProgress($orderIncrementId . ' => Trigger Netsuite as InvoiceSave failed.');
                    continue;
                }
            }
            $this->logProgress($orderIncrementId . ' => Trigger Netsuite as InvoiceSave success.');
            
            //- insert ke table message dgn body sebagai order_place dan entity_id nya
            if (empty($orderNetsuiteInternalId) || is_null($orderNetsuiteInternalId)) {
                if (!$this->triggerNetsuiteOrderPlace($orderId)) {
                    $this->logProgress($orderIncrementId . ' => Trigger Netsuite as OrderPlace failed.');
                    continue;
                }
                $this->logProgress($orderIncrementId . ' => Trigger Netsuite as OrderPlace failed.');
            }
            
            //- insert ke table message dgn body sebagai customer_save dan entity_id dari table sales_flat_order_adress dgn addres_type nya shipping dgn parent_id nya adalah entity_id sales order yg di atas
            if (!$this->triggerNetsuiteCustomerSave($orderId)) {
                $this->logProgress($orderIncrementId . ' => Trigger Netsuite as CustomerSave failed.');
                continue;
            }
            $this->logProgress($orderIncrementId . ' => Trigger Netsuite as CustomerSave success.');

            $this->logProgress($orderIncrementId . ' => All step process success.');
            unset ($order);
        }
    }
    
    protected function normalizeOrder($orderId) {
        $sql = sprintf("
            UPDATE `%s`
            SET `state` = 'new', `status` = 'pending', `base_discount_invoiced` = 0.0000, `base_subtotal_invoiced` = 0.0000, `base_total_invoiced` = 0.0000, `base_total_invoiced_cost` = 0, `base_total_paid` = 0.0000, `discount_invoiced` = 0.0000, `subtotal_invoiced` = 0.0000, `total_invoiced` = 0.0000, `total_paid` = 0.0000, `base_total_due` = `base_total_paid`, `total_due` = `total_paid`
            WHERE `entity_id` IN (%d);
        ", $this->resource->getTableName('sales/order'), $orderId);
        
        if (!$this->write->query($sql)) {
            return false;
        }
        
        return true;
    }
    
    protected function normalizeOrderItem($orderId) {
        $sql = sprintf("
            UPDATE `%s`
            SET `qty_invoiced` = 0.0000, `discount_invoiced` = 0.0000, `base_discount_invoiced` = 0.0000, `row_invoiced` = 0.0000, `base_row_invoiced` = 0.0000 
            WHERE `order_id` IN (%d);
        ", $this->resource->getTableName('sales/order_item'), $orderId);
        
        if (!$this->write->query($sql)) {
            return false;
        }
        
        return true;
    }

    protected function normalizeOrderPayment($orderId) {
        $sql = sprintf("
            UPDATE `%s`
            SET `base_shipping_captured` = NULL, `shipping_captured` = NULL, `base_amount_paid` = NULL, `amount_paid` = NULL 
            WHERE `parent_id` IN (%d);
        ", $this->resource->getTableName('sales/order_payment'), $orderId);
        
        if (!$this->write->query($sql)) {
            return false;
        }
        
        return true;
    }
    
    protected function createInvoice($orderIncrementId, $order) {
    // disable the point reward observer when running this code
    Mage::app()->getConfig()->getEventConfig('global', 'sales_order_invoice_pay');
    $path = 'global/events/sales_order_invoice_pay/observers/points_invoice_pay_observer/type';
    Mage::app()->getConfig()->setNode($path, 'disabled');
        try {
            //- check invoice already exist?
            if (!$order->hasInvoices()) {
                //- can create invoice?
                if (!$order->canInvoice()) {
                    $this->logProgress($orderIncrementId . ' => Cannot create an invoice.');

                    return false;
                }

                $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();

                if (!$invoice->getTotalQty()) {
                    $this->logProgress($orderIncrementId . ' => Cannot create an invoice without products.');

                    return false;
                }

                $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE);
                $invoice->setGrandTotal($order->getGrandTotal());
                $invoice->setBaseGrandTotal($order->getBaseGrandTotal());
                $invoice->register();
                $transaction = Mage::getModel('core/resource_transaction')
                    ->addObject($invoice)
                    ->addObject($invoice->getOrder());
                $transaction->save();
                $this->logProgress($orderIncrementId . ' => Create invoice success.');
            }
            else {
                $this->logProgress($orderIncrementId . ' => Order has invoices.');
                
                foreach ($order->getInvoiceCollection() as $orderInvoice) {
                    $invoice = $orderInvoice;
                    break;
                }
            }
            
            $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true, 'Create invoice by system');
            $order->save();
            $this->logProgress($orderIncrementId . ' => Update status order success.');
            
            return $invoice;
        }
        catch (Mage_Core_Exception $e) {
            $this->logProgress($orderIncrementId . ' => ' . $e->getMessage());
            
            return false;
        }
    }

    protected function removeMessageInvoice($invoiceId) {
        $this->logProgress('Start delete message invoice_save #' . $invoiceId);
        
        $sql = sprintf("SELECT `message_id` FROM `message` WHERE `body` = 'invoice_save|%d' ORDER BY `message_id`", $invoiceId);
        $result = $this->read->fetchAll($sql);
        
        if (!$result) {
            $this->logProgress('Message invoice_save #' . $invoiceId . ' not found');
            
            return false;
        }
        
        foreach ($result as $row) {
            if (!$this->deleteMessageInvoice($invoiceId, $row['message_id'])) {
                $this->logProgress('Delete message invoice_save #' . $invoiceId . ' failed');
                
                return false;
            }
            
            break;
        }
        
        $this->logProgress('Success delete message invoice_save #' . $invoiceId);
        
        return true;
    }
    
    protected function deleteMessageInvoice($invoiceId, $messageId) {
        $sql = sprintf("DELETE FROM message WHERE message_id = %d AND body = 'invoice_save|%d' LIMIT 1", $messageId, $invoiceId);
        
        if (!$this->write->query($sql)) {
            return false;
        }
        
        return true;
    }
    
    protected function triggerNetsuiteInvoiceSave($orderId, $invoiceId) {
        $this->logProgress('Start trigger netsuite as invoice save #' . $orderId);
        
        $sql = sprintf("
            INSERT INTO `message`(`message_id`, `queue_id`, `handle`, `body`, `md5`, `timeout`, `created`, `priority`)
            VALUES(NULL, 1, NULL, 'invoice_save|%d', md5('invoice_save|%d'), NULL, unix_timestamp(), 0);
        ", $invoiceId, $invoiceId);
        
        if ($this->write->query($sql)) {
            $this->logProgress('Success trigger netsuite as invoice save #' . $orderId);
            
            return true;
        }
        
        return false;
    }

    protected function triggerNetsuiteOrderPlace($orderId) {
        $this->logProgress('Start trigger netsuite as order place #' . $orderId);
        
        //- check message
        if ($this->isExistNetsuiteOrderPlace($orderId)) {
            $this->logProgress('netsuite as order place #' . $orderId . ' already exist');
            
            return true;
        }
        
        $sql = sprintf("
            INSERT INTO `message`(`message_id`, `queue_id`, `handle`, `body`, `md5`, `timeout`, `created`, `priority`)
            VALUES(NULL, 1, NULL, 'order_place|%d', md5('order_place|%d'), NULL, unix_timestamp(), 0);
        ", $orderId, $orderId);
        
        if ($this->write->query($sql)) {
            $this->logProgress('Success trigger netsuite as order place #' . $orderId);
            
            return true;
        }
        
        return false;
    }
    
    protected function isExistNetsuiteOrderPlace($orderId) {
        $sql = sprintf("SELECT `message_id` FROM `message` WHERE `body` = 'order_place|%d' LIMIT 1", $orderId);
        $messageId = $this->read->fetchOne($sql);
        
        if (!$messageId) {
            return false;
        }
        
        return true;
    }

    protected function triggerNetsuiteCustomerSave($orderId) {
        $this->logProgress('Start trigger netsuite as customer save #' . $orderId);
        
        $collection = Mage::getModel('sales/order_address')->getCollection();
        $collection->addFieldToSelect(array ('customer_address_id'));
        $collection->addFieldToFilter('`main_table`.`parent_id`', array ('eq' => $orderId));
        $collection->addFieldToFilter('address_type', 'shipping');
        //$collection->getFirstItem();
        
        if ($collection->getSize() == 0) {
            return false;
        }
        
        $orderAddress = $collection->getData();
        $customerAddressId = $orderAddress[0]['customer_address_id'];
        
        //- check message
        if ($this->isExistNetsuiteCustomerSave($customerAddressId)) {
            $this->logProgress('netsuite as customer save #' . $customerAddressId . ' already exist');
            
            return true;
        }
        
        $sql = sprintf("
            INSERT INTO `message`(`message_id`, `queue_id`, `handle`, `body`, `md5`, `timeout`, `created`, `priority`)
            VALUES(NULL, 1, NULL, 'customer_save|%d', md5('customer_save|%d'), NULL, unix_timestamp(), 0);
        ", $customerAddressId, $customerAddressId);
        
        if ($this->write->query($sql)) {
            $this->logProgress('Success trigger netsuite as customer save #' . $orderId);
            
            return true;
        }
        
        return true;
    }
    
    protected function isExistNetsuiteCustomerSave($customerAddressId) {
        $sql = sprintf("SELECT `message_id` FROM `message` WHERE `body` = 'customer_save|%d' LIMIT 1", $customerAddressId);
        $messageId = $this->read->fetchOne($sql);
        
        if (!$messageId) {
            return false;
        }
        
        return true;
    }

    protected function parseConfig($data) {
        return explode(',', $data);
    }
    
    protected function critical($message) {
        $this->logProgress($message);
        exit(1);
    }
    
    protected function isTest() {
        if ($this->getArg('test')) {
            return true;
        }
        
        return false;
    }

    protected function logProgress($message, $veritrans_log = false) {
        if ($veritrans_log) {
            $this->writeLogVeritrans($message);
        } else {
            $this->writeLog($message);
        }
        
        if ($this->getArg('verbose')) {
            echo $message . "\n";
        }
    }
    
    public function writeLog($message) {
        Mage::log($message, null, 'bulkCreateInvoice.log');
    }

    public function writeLogVeritrans($message) {
        Mage::log($message, null, 'veritrans_status.log');
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
}

$shell = new bulkCreateInvoice();
$shell->run();

