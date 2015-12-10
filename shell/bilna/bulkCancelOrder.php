<?php
/**
 * Description of BulkCancelOrder
 *
 * @author Bilna Development Team <development@bilna.com>
 */

require_once dirname(__FILE__) . '/../abstract.php';

class BulkCancelOrder extends Mage_Shell_Abstract {
    
    public function run() {
        $csvObject = new Varien_File_Csv();

        $directory = 'files/cancelorder/notyet';
        $donedirectory = 'files/cancelorder/done';

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
            echo $e->getMessage() . "\n";
            Mage::log('Csv: ' . $file . ' - getCsvData() error - '. $e->getMessage(), Zend_Log::ERR, 'exception.log', true);
            return false;
        }
    }

    protected function cancel_order($increment_id)
    {
        $orderModel = Mage::getModel('sales/order');
        $orderModel->load($increment_id, 'increment_id');

        if($orderModel->canCancel())
        {
            $comment = 'Pesanan ini dibatalkan dikarenakan oleh beberapa kendala teknis yang mengakibatkan pesanan Anda terbuat lebih dari satu kali di sistem kami. Jika Anda telah menggunakan voucher pada saat berbelanja, kami telah memilih order yang tidak menggunakan voucher untuk dibatalkan.';

            $orderModel->cancel();
            $orderModel->setStatus('canceled');
            $history = $orderModel->addStatusHistoryComment($comment, true);
            $history->setIsCustomerNotified(1)->save();
            $orderModel->save();
            $orderModel->sendOrderUpdateEmail($notify = true, $comment); 
            Mage::log('Auto Cancelling Order : ' . $increment_id, null, 'autocancelorder.log', true);
        }
    }
}

$shell = new BulkCancelOrder();
$shell->run();