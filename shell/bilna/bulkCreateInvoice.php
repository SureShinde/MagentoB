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

    public function init() {
        $this->resource = Mage::getSingleton('core/resource');
        $this->write = $this->resource->getConnection('core_write');
        $this->read = $this->resource->getConnection('core_read');
    }

    public function run() {
        $this->init();
        $orderIncrementIds = $this->getOrderIncrementIds();
        
        //- step 1 => get order where status & state is processing
        if (!$orderIncrementIds) {
            $this->critical('Order not found.');
        }
        
        //- step 2 => process order (normalisasi)
        $this->processOrders($orderIncrementIds);
        $this->logProgress('Process all order successfully.');
    }
    
    protected function getOrderIncrementIds() {
        if (!$this->getArg('orders')) {
            return false;
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
            
            //- create invoice
            $invoice = $this->createInvoice($orderId);
            
            if (!$invoice) {
                $this->logProgress($orderIncrementId . ' => Invoice not found.');
                continue;
            }
            
            $invoiceId = $invoice->getId();
            
            //- remove message invoice
            if (!$this->removeMessageInvoice($invoiceId)) {
                $this->logProgress($orderIncrementId . ' => Remove queue InvoiceSave failed.');
                $this->logProgress('Remove message invoice #' . $invoice->getId() . ' failed');
                continue;
            }
            $this->logProgress($orderIncrementId . ' => Remove queue InvoiceSave success.');
            
            //- insert ke table message dgn body sebagai invoice_save dan entity_id nya
            if (!$this->triggerNetsuiteInvoiceSave($orderId, $invoiceId)) {
                $this->logProgress($orderIncrementId . ' => Trigger Netsuite as InvoiceSave failed.');
                continue;
            }
            $this->logProgress($orderIncrementId . ' => Trigger Netsuite as InvoiceSave success.');
            
            //- insert ke table message dgn body sebagai order_place dan entity_id nya
            if (!$this->triggerNetsuiteOrderPlace($orderId)) {
                $this->logProgress($orderIncrementId . ' => Trigger Netsuite as OrderPlace failed.');
                continue;
            }
            $this->logProgress($orderIncrementId . ' => Trigger Netsuite as OrderPlace failed.');
            
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
            WHERE `order_id` IN (%d)
        ", $this->resource->getTableName('sales/order_item'), $orderId);
        
        if (!$this->write->query($sql)) {
            return false;
        }
        
        return true;
    }
    
    protected function createInvoice($orderIncrementId, $order) {
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
        exit(1);
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
        Mage::log($message, null, 'bulkCreateInvoice.log');
    }
}

$shell = new bulkCreateInvoice();
$shell->run();
