<?php
/**
 * Description of BulkCancelOrder
 *
 * @author Bilna Development Team <development@bilna.com>
 */

require_once dirname(__FILE__) . '/../abstract.php';

class BulkCancelOrder extends Mage_Shell_Abstract {
    
    public function run() {
        // get lock
        $mutex = new RocketWeb_Netsuite_Model_Mutex('bulkcancelorder_cron_lock');
            
        if (!$mutex->getLock()) {
            die ("no lock!!!\n");
        }

        // START PROCESS
        echo "START PROCESS BULK CANCEL ORDER\n";
        echo "-------------------------------\n";

        $csvObject = new Varien_File_Csv();

        $directory = 'files/cancelorder/notyet';
        $donedirectory = 'files/cancelorder/done';

        $error = false;

        try 
        {
            if (file_exists($directory)) 
            {
                // get CSV file inside the directory
                foreach (glob($directory."/*.csv") as $filename) {
                    $data = $csvObject->getData($filename);
                    // read data line by line
                    foreach ($data as $lines => $line) {
                        $this->cancel_order($line[0]);
                    }
                    // move the processed file to the done directory
                    rename($filename, $donedirectory."/".basename($filename));
                }
            }
        } catch (Exception $e) {
            $error = true;
            echo "ERROR : " . $e->getMessage() . "\n";
            Mage::log('Csv: ' . $file . ' - getCsvData() error - '. $e->getMessage(), Zend_Log::ERR, 'exception.log', true);
        }

        echo "--------------------------------\n";
        echo "FINISH PROCESS BULK CANCEL ORDER";
        if ($error)
            echo " WITH ERROR";
        echo "\n\n";

        echo "Please check file autocancelorder.log at var/log to view all cancelled orders\n";

        $mutex->releaseLock();
    }

    protected function cancel_order($increment_id)
    {
        $orderModel = Mage::getModel('sales/order');
        $orderModel->load($increment_id, 'increment_id');

        // check whether the order can be cancelled
        if($orderModel->canCancel())
        {
            $entity_id = $orderModel->getEntityId();
            echo "cancelling order #" . $increment_id . "\n";

            $comment = 'Pesanan ini dibatalkan dikarenakan oleh beberapa kendala teknis yang mengakibatkan pesanan Anda terbuat lebih dari satu kali di sistem kami. Jika Anda telah menggunakan voucher pada saat berbelanja, kami telah memilih order yang tidak menggunakan voucher untuk dibatalkan.';

            // cancel the order
            $orderModel->cancel();
            $orderModel->setStatus('canceled');
            $history = $orderModel->addStatusHistoryComment($comment, true);
            $history->setIsCustomerNotified(1)->save();

            // remove the entity from queue
            $this->remove_from_netsuite_queue($entity_id);

            $orderModel->save();
            $orderModel->sendOrderUpdateEmail($notify = true, $comment);
            Mage::log('Auto Cancelling Order : ' . $increment_id, null, 'autocancelorder.log', true);
        }
    }

    protected function remove_from_netsuite_queue($entity_id)
    {
        // database write adapter 
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        $write->beginTransaction();
        $condition = array($write->quoteInto('body=?', 'order_place|'.$entity_id));
        $write->delete("message", $condition);
        $write->commit();
    }
}

$shell = new BulkCancelOrder();
$shell->run();