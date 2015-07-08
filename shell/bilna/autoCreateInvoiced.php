<?php
/**
 * Description of autoCreateInvoiced
 *
 * @author Bilna Development Team <development@bilna.com>
 */

require_once dirname(__FILE__) . '/../abstract.php';

class autoCreateInvoiced extends Mage_Shell_Abstract {
    protected $resource;
    protected $write;
    protected $read;
    protected $dirLog = '';
    protected $paymentBca = array ('klikbca', 'klikpay');
    protected $paymentAllowed = array ();
    protected $state = 'processing';
    protected $status = 'processing';
    protected $orders;

    public function init() {
        $this->resource = Mage::getSingleton('core/resource');
        $this->write = $this->resource->getConnection('core_write');
        $this->read = $this->resource->getConnection('core_read');
        $this->dirLog = Mage::getBaseDir('log');
        $this->paymentAllowed = array_merge($this->paymentBca, $this->parseConfig(Mage::getStoreConfig('bilna_module/success_page/payment_cc')));
    }

    public function run() {
        //- step 1 => get order where status & state is processing
        if (!$this->getOrders()) {
            $this->critical('Order not found.');
        }
        
        //- step 2 => process order (normalisasi)
        if (!$this->processOrders()) {
            $this->critical('Process order failed.');
        }
        
        $this->logProgress('Process all order successfully.');
    }
    
    protected function getOrders() {
        $collection = Mage::getModel('sales/order')->getCollection();
        $collection->getSelect()->joinLeft(
            array ('payment' => $this->resource->getTableName('sales/order_payment')),
            '`main_table`.`entity_id` = `payment`.`parent_id`',
            array ('method' => 'payment.method')
        );
        $collection->getSelect()->joinLeft(
            array ('invoice' => $this->resource->getTableName('sales/invoice')),
            '`main_table`.`entity_id` = `invoice`.`order_id`',
            array ('invoice_id' => 'invoice.entity_id')
        );
        $collection->addFieldToSelect(array ('state', 'status'));
        $collection->addFieldToFilter('`main_table`.`state`', $this->state);
        $collection->addFieldToFilter('`main_table`.`status`', $this->status);
        $collection->addFieldToFilter('`payment`.`method`', array ('in' => $this->paymentAllowed));
        $collection->addFieldToFilter('`invoice`.`entity_id`', array ('null' => true));
        
        //- testing
        if ($this->isTest()) {
            $collection->addFieldToFilter('`main_table`.`entity_id`', array ('eq' => 128836));
            $collection->getSelect()->limit(1);
        }
        
        if ($collection->getSize() > 0) {
            $this->orders = $collection;
            
            return true;
        }
        
        return false;
    }

    protected function processOrders() {
        foreach ($this->orders as $order) {
            $orderId = $order->getId();
            
            //- normalisasi order
            if (!$this->normalizeOrder($orderId)) {
                $this->logProgress('Normalize order #' . $orderId . ' failed');
                
                return false;
            }
            
            //- normalisasi order item
            if (!$this->normalizeOrderItem($orderId)) {
                $this->logProgress('Normalize order item #' . $orderId . ' failed');
                
                return false;
            }
            
            //- create invoice
            $invoice = $this->createInvoice($orderId);
            if (!$invoice) {
                $this->logProgress('Create invoice for order #' . $orderId . ' failed');
                
                return false;
            }
            
            //- remove message invoice
            if (!$this->removeMessageInvoice($invoice->getId())) {
                $this->logProgress('Remove message invoice #' . $invoice->getId() . ' failed');
                
                return false;
            }
            
            //- insert ke table message dgn body sebagai order_place dan entity_id nya
            if (!$this->triggerNetsuiteOrderPlace($orderId)) {
                $this->logProgress('Trigger Netsuite as Order Place #' . $orderId . ' failed');
                
                return false;
            }
            
            //- insert ke table message dgn body sebagai customer_save dan entity_id dari table sales_flat_order_adress dgn addres_type nya shipping dgn parent_id nya adalah entity_id sales order yg di atas
            if (!$this->triggerNetsuiteCustomerSave($orderId)) {
                $this->logProgress('Trigger Netsuite as Customer Save #' . $orderId . ' failed');
                
                return false;
            }
        }
        
        return true;
    }
    
    protected function normalizeOrder($orderId) {
        $this->logProgress('Start nomalize order #' . $orderId);
        
        $sql = sprintf("
            UPDATE `%s`
            SET `state` = 'new', `status` = 'pending', `base_discount_invoiced` = 0.0000, `base_subtotal_invoiced` = 0.0000, `base_total_invoiced` = 0.0000, `base_total_invoiced_cost` = 0, `base_total_paid` = 0.0000, `discount_invoiced` = 0.0000, `subtotal_invoiced` = 0.0000, `total_invoiced` = 0.0000, `total_paid` = 0.0000, `base_total_due` = `base_total_paid`, `total_due` = `total_paid`
            WHERE `entity_id` IN (%d);
        ", $this->resource->getTableName('sales/order'), $orderId);
        
        if ($this->write->query($sql)) {
            $this->logProgress('Success nomalize order #' . $orderId);
            
            return true;
        }
        
        return true;
    }
    
    protected function normalizeOrderItem($orderId) {
        $this->logProgress('Start nomalize order #' . $orderId . ' item');
        
        $sql = sprintf("
            UPDATE `%s`
            SET `qty_invoiced` = 0.0000, `discount_invoiced` = 0.0000, `base_discount_invoiced` = 0.0000, `row_invoiced` = 0.0000, `base_row_invoiced` = 0.0000 
            WHERE `order_id` IN (%d)
        ", $this->resource->getTableName('sales/order_item'), $orderId);
        
        if ($this->write->query($sql)) {
            $this->logProgress('Success nomalize order #' . $orderId . ' item');
            
            return true;
        }
        
        return true;
    }
    
    protected function createInvoice($orderId) {
        $this->logProgress('Start create invoice for order #' . $orderId);
        $order = Mage::getModel('sales/order')->load($orderId);
        
        try {
            if (!$order->getId()) {
                $this->logProgress('Order not found.');
                
                return false;
            }
            
            if (!$order->canInvoice()) {
                $this->logProgress('Cannot create an invoice.');
                
                return false;
            }

            $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();

            if (!$invoice->getTotalQty()) {
                $this->logProgress('Cannot create an invoice without products.');
                
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
            $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true, 'Create invoice by system');
            $order->save();
            
            $this->logProgress('Success create invoice for order #' . $orderId);
            
            return $invoice;
        }
        catch (Mage_Core_Exception $e) {
            $this->logProgress($e->getMessage());
            
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
            $this->logProgress('Success trigger netsuite as order place #' . $orderId);
            
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
        exit;
    }
    
    protected function isTest() {
        if ($this->getArg('test')) {
            return true;
        }
        
        return false;
    }

    protected function logProgress($message) {
        $this->writeLog($message);
        
        if ($this->getArg('verbose')) {
            echo $message . "\n";
        }
    }
    
    public function writeLog($message) {
        //- base directory log
        $baseDir = Mage::getBaseDir('log');
        
        if (!file_exists($baseDir)) {
            mkdir($baseDir, 0777, true);
        }
        
        //- directory module
        $dir = 'autoInvoiced';
        
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        
        $filename = $baseDir . DS . $dir . DS . date('Ymd') . '.log';
        
        if (file_exists($filename)) {
            $handle = fopen($filename, 'a');
        }
        else {
            $handle = fopen($filename, 'w'); 
        }
        
        fwrite($handle, $message . "\n");
        fclose($handle);
    }
}

$shell = new autoCreateInvoiced();
$shell->init();
$shell->run();
