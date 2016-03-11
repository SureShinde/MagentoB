<?php
/**
 * Description of Bilna_Worker_Order_GenerateInvoices
 *
 * @author Bilna Development Team <development@bilna.com>
 */

require_once dirname(__FILE__) . '/Order.php';

class Bilna_Worker_Order_GenerateInvoices extends Bilna_Worker_Order_Order {
    protected $_tubeAllow = 'vt_charge';

    protected function _process() {
        try {
            $this->_queueSvc->watch($this->_tubeAllow);
            $this->_queueSvc->ignore($this->_tubeIgnore);

            while ($job = $this->_queueSvc->reserve()) {
                $dataArr = json_decode($job->getData(), true);
                $dataObj = json_decode($job->getData());
                
                $order = Mage::getModel('sales/order')->load($dataObj->order_id);
                $orderNo = $order->getIncrementId();
                $paymentCode = $order->getPayment()->getMethodInstance()->getCode();
                $status = Mage::getModel('paymethod/vtdirect')->updateOrder($order, $paymentCode, $dataObj);
                
                if ($status && $this->queueOrderPlaceForNetsuite($order, $paymentCode)) {
                    $this->_queueSvc->delete($job);
                    $this->_logProgress("#{$orderNo} Insert to DB => success");
                }
                else {
                    $this->_queueSvc->bury($job);
                    $this->_logProgress("#{$orderNo} Insert to DB => failed");
                }
            }
        }
        catch (Exception $ex) {
            $this->_queueSvc->bury($job);
            $this->_critical($ex->getMessage());
        }
    }
}

$worker = new Bilna_Worker_Order_GenerateInvoices();
$worker->run();
