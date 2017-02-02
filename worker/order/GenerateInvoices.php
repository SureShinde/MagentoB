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
                
                if (isset($dataObj->payment_type) && ($dataObj->payment_type == "postpay")) {
                    if (!$order->canInvoice()) {
                        Mage::log("Postpay cannot create invoice for order ".$incrementId);
                        Mage::throwException(Mage::helper('core')->__('Cannot create an invoice for order '.$incrementId));
                        continue;
                    }

                    $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();
                    if (!$invoice->getTotalQty()) {
                        Mage::throwException(Mage::helper('core')->__('Cannot create an invoice without products.'));
                    }
                    $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE);
                    $invoice->register();
                    $transaction = Mage::getModel('core/resource_transaction')->addObject($invoice)->addObject($order);
                    $transaction->save();
                    $invoice->sendEmail(true, '');
                    Mage::log("Postpay create invoice for order ".$incrementId);
                    $this->_logProgress("#{$incrementId} Process Invoice => success");
                    $this->_queueSvc->delete($job);
                    continue;
                }

                $paymentCode = $order->getPayment()->getMethodInstance()->getCode();
                $status = Mage::getModel('paymethod/vtdirect')->updateOrder($order, $paymentCode, $dataObj);
                
                if ($status === false) {
                    $this->_logProgress("#{$incrementId} Process Invoice => failed");
                    $this->_queueSvc->bury($job);
                }
                else {
                    if ($status === true) {
                        Mage::dispatchEvent('sales_order_place_after', array ('order' => $order));
                        $this->_logProgress("#{$incrementId} Process Invoice => success");
                    }
                    else {
                        $this->_logProgress("#{$incrementId} Process Invoice => skip");
                    }

                    $this->_queueSvc->delete($job);
                }
            }
        }
        catch (Exception $e) {
            Mage::logException($e);
            $this->_logProgress($e->getMessage());
        }
    }
}

$worker = new Bilna_Worker_Order_GenerateInvoices();
$worker->run();
