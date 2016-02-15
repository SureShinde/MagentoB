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
        $pheanstalk = new Pheanstalk('127.0.0.1');
        
        try {
            $pheanstalk->watch('invoice');
            $pheanstalk->ignore('default');

            while ($job = $pheanstalk->reserve()) {
                $dataArr = json_decode($job->getData());
                $insertLog = Mage::getModel('paymethod/veritrans')->addData($dataArr)->insert()->save();
                
                $order = Mage::getModel('sales/order')->load($dataArr->order_id);
                $paymentCode = $order->getPayment()->getMethodInstance()->getCode();
                $charge = $dataArr;
                $status = Mage::getModel('paymethod/vtdirect')->updateOrder($order, $paymentCode, $charge);

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
