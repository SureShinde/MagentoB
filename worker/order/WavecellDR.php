<?php
/**
 * Description of Bilna_Worker_Order_GenerateInvoices
 *
 * @author Bilna Development Team <development@bilna.com>
 */

require_once dirname(__FILE__) . '/Order.php';

class Bilna_Worker_Order_WavecellDR extends Bilna_Worker_Order_Order {
    protected $_tubeAllow = 'wavecell_dr';

    protected function _process() {
        try {
            $this->_queueSvc->watch($this->_tubeAllow);
            $this->_queueSvc->ignore($this->_tubeIgnore);
            $smsDrModel = Mage::getModel('smsverification/smsdr');
            while ($job = $this->_queueSvc->reserve()) {
                $dataObj = json_decode($job->getData());
                while(true) {
                    if(($dataObj->timestamp + 300) >= strtotime("now")) {
                        break;
                    }
                }
                Mage::log("Wavecell Worker get Incoming que: ".json_encode($dataObj));
                $code = $dataObj->code;
                $status = $dataObj->status;
                if((strtoupper($status) == "DELIVERED TO CARRIER") || (strtoupper($status) == "DELIVERED TO DEVICE")) {
                    $detailData = $smsDrModel->getCollection()->addFilter('code',array('equal' => $code))->getFirstItem()->getData();
                    if ($detailData) {
                        $order = Mage::getModel('sales/order')->load($detailData['order_id']);
                        Mage::log("Wavecell Worker get Order: ".$detailData['order_id'].", Code: ".$code);
                        if ($order) {
                            $paymentMethod = $order->getPayment()->getMethodInstance()->getTitle();
                            //print $paymentMethod."\n";continue;
                            if ($paymentMethod == Mage::getStoreConfig('payment/cod/title')) {
                                $order->setStatus(Mage::getStoreConfig('payment/cod/order_status'),true)->save();
                                Mage::log("Wavecell Worker update Order: ".$detailData['order_id'].", Code: ".$code.", Value: ".$paymentMethod." ==> ".Mage::getStoreConfig('payment/cod/order_status'));
                            }
                        }
                        $smsDrModel->load($detailData['sms_id'])->delete();
                    }
                }
                $this->_queueSvc->delete($job);
            }
        }
        catch (Exception $e) {
            Mage::logException($e);
            $this->_logProgress($e->getMessage());
        }
    }
}

$worker = new Bilna_Worker_Order_WavecellDR();
$worker->run();
