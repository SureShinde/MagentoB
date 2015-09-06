<?php
/**
 * Description of GenerateMegamenu
 *
 * @author Bilna Development Team <development@bilna.com>
 */

require_once dirname(__FILE__) . '/../../abstract.php';

use Pheanstalk\Pheanstalk;

class GenerateInvoices extends Mage_Shell_Abstract 
{
	public function run()
	{
		$pheanstalk = new Pheanstalk('127.0.0.1');

		try{
			$job = $pheanstalk
                ->watch("invoice")
                ->ignore('default')
                ->reserve();
            $dataJson = json_decode($job->getData());

            $order = Mage::getModel('sales/order')->load($dataJson->order_id);
			$paymentCode = $order->getPayment()->getMethodInstance()->getCode();
			$charge = $dataJson;
			Mage::getModel('paymethod/vtdirect')->updateOrder($order, $paymentCode, $charge);

		}catch(Exception $e){
			Mage::logException($e);
		}

	}
}

$shell = new GenerateInvoices();
$shell->run();