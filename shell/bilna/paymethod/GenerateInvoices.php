<?php
/**
 * Description of GenerateMegamenu
 *
 * @author Bilna Development Team <development@bilna.com>
 */

require_once dirname(__FILE__) . '/../../abstract.php';

use Pheanstalk\Pheanstalk;

class GenerateInvoices extends Mage_Shell_Abstract {
    public function run() {
        $hostname = Mage::getStoreConfig('bilna_queue/beanstalkd/settings/hostname');
        $pheanstalk = new Pheanstalk($hostname);
        
        try {
            $pheanstalk->watch('invoice');
            $pheanstalk->ignore('default');

            while ($job = $pheanstalk->reserve()) {
                $dataArr = json_decode($job->getData(), true);
                $dataObj = json_decode($job->getData());
                
                $veritransModel = Mage::getModel('paymethod/veritrans');
                $veritransModel->addData($dataArr);
                $veritransModel->insert();
                $veritransModel->save();
                
                $order = Mage::getModel('sales/order')->load($dataObj->order_id);
                $paymentCode = $order->getPayment()->getMethodInstance()->getCode();
                $status = Mage::getModel('paymethod/vtdirect')->updateOrder($order, $paymentCode, $dataObj);

                if ($status) {
                    $pheanstalk->delete($job);
                }
                else {
                    $pheanstalk->bury($job);
                }
            }
        }
        catch (Exception $e) {
            $pheanstalk->bury($job);
            Mage::logException($e);
        }
        
        echo "\nFINISH";
    }
}

$shell = new GenerateInvoices();
$shell->run();
