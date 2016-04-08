<?php
/**
 * Description of Bilna_Worker_Order_GenerateInvoices
 *
 * @author Bilna Development Team <development@bilna.com>
 */

require_once dirname(__FILE__) . '/Order.php';

class Bilna_Worker_Order_GenerateInvoices extends Bilna_Worker_Order_Order {
    protected $_tubeAllow = 'invoice';

    protected function _process() {
        try {
            $this->_queueSvc->watch($this->_tubeAllow);
            $this->_queueSvc->ignore($this->_tubeIgnore);

            while ($job = $this->_queueSvc->reserve()) {
                $dataObj = json_decode($job->getData());
                $incrementId = $dataObj->order_id;
                $order = Mage::getModel('sales/order')->loadByIncrementId($incrementId);
                
                if (!$order) {
                    $this->_queueSvc->delete($job);
                    $this->_logProgress("#{$incrementId} Process Invoice => failed, Order not found.");
                    continue;
                }
                
                $paymentCode = $order->getPayment()->getMethodInstance()->getCode();
                $status = Mage::getModel('paymethod/vtdirect')->updateOrder($order, $paymentCode, $dataObj);
                
                if ($status) {
                    Mage::dispatchEvent('sales_order_place_after', array ('order' => $order));
                    $this->_queueSvc->delete($job);
                    $this->_logProgress("#{$incrementId} Process Invoice => success");
                }
                else {
                    $this->_queueSvc->bury($job);
                    $this->_logProgress("#{$incrementId} Process Invoice => failed");
                }
            }
        }
        catch (Exception $ex) {
            $this->_queueSvc->bury($job);
            $this->_logProgress($ex->getMessage());
        }
    }
}

$worker = new Bilna_Worker_Order_GenerateInvoices();
$worker->run();
