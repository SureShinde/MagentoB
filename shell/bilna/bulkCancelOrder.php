<?php
/**
 * Description of BulkCancelOrder
 *
 * @author Bilna Development Team <development@bilna.com>
 */

require_once dirname(__FILE__) . '/../abstract.php';

class BulkCancelOrder extends Mage_Shell_Abstract {
    protected $start;
    protected $end;
    protected $run_mode = "db";

    public function run() {

        $modes = $this->getModes();

        if ($modes) 
        {
            // get lock
            $mutex = new RocketWeb_Netsuite_Model_Mutex('bulkcancelorder_cron_lock');
                
            // make sure there is nothing locking the cron
            if (!$mutex->getLock()) {
                die ("no lock!!!\n");
            }

            // check whether it will be run via DB or File
            if ($this->run_mode == "db")
                $this->run_by_db();
            else
            if ($this->run_mode == "file")
                $this->run_by_file();

            // release the cron
            $mutex->releaseLock();
        }
        else {
            echo $this->usageHelp();
        }
    }

    protected function run_by_db()
    {
        // START PROCESS
        echo "START PROCESS BULK CANCEL ORDER\n";
        echo "-------------------------------\n";

        $directory = 'files/cancelorder';
        $config_file = 'sql_config';
        $queryFromConfig = file_get_contents($directory . '/' . $config_file);

        $error = false;

        if (trim($queryFromConfig) != '') {
            try 
            {
                $readConn = Mage::getSingleton('core/resource')->getConnection('core_read');
                $query = $queryFromConfig;

                $results = $readConn->fetchAll($query);

                foreach($results as $key => $value) {
                    $this->cancel_order($value['increment_id']);
                }
            }
            catch (Exception $e) {
                $error = true;
                echo "ERROR : " . $e->getMessage() . "\n";
                Mage::log('Csv: ' . $file . ' - getCsvData() error - '. $e->getMessage(), Zend_Log::ERR, 'exception.log', true);
            }
        }

        echo "--------------------------------\n";
        echo "FINISH PROCESS BULK CANCEL ORDER";
        if ($error)
            echo " WITH ERROR";
        echo "\n\n";

        echo "Please check file autocancelorder.log at var/log to view all cancelled orders\n";
    }

    protected function run_by_file()
    {
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

    protected function getModes() {
        $possibleModes = $this->getPossibleModes();
        /*
        $startParam = $this->getArg('start');
        $endParam = $this->getArg('end');
        */
        $methodParam = $this->getArg('method');

        /*
        if ($startParam && $endParam) {
            $this->start = $startParam;
            $this->end = $endParam;
        }
        else {
            return null;
        }
        */
        
        if ($methodParam) {
            if ($methodParam == 'file' || $methodParam == 'db')
                $this->run_mode = $methodParam;
            else
                return false;
        }

        return true;
    }

    protected function getPossibleModes() {
        return array ('method');
    }

    public function usageHelp() {
        return <<<USAGE
Usage:  php shell/bilna/bulkCancelOrder.php -- [options]
  --method                      file or db ( default is db ) (OPTIONAL)
  NOTE : start and end must both exist ; method is optional

USAGE;
    }
}

$shell = new BulkCancelOrder();
$shell->run();