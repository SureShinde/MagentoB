<?php
/**
 * Description of Vtdirect
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Paymethod_Model_Vtdirect extends Mage_Core_Model_Abstract {
    public function updateOrder($order, $paymentCode, $charge) {
        $paymentCode = $order->getPayment()->getMethodInstance()->getCode();
        $orderStatus = $order->getStatus();
        $isVtdirectNotification = $this->isVtdirectNotification($charge, $paymentCode);

        // check order status if processing/complete then ignore
        if (in_array($orderStatus, Mage::helper('paymethod/vtdirect')->getStatusOrderIgnore()) && !$isVtdirectNotification) {
            return true;
        }

        $message = $charge->status_message;
        $transactionStatus = $charge->transaction_status;
        $fraudStatus = $charge->fraud_status;
        $orderStatusAllow = $this->getNotificationOrderStatusAllow();

        if ($isVtdirectNotification) {
            if ($transactionStatus == 'settlement' && in_array($orderStatus, $orderStatusAllow)) {
                if ($order->canInvoice()) {
                    $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();

                    if ($invoice->getTotalQty()) {
                        $invoice->register();
                        $transaction = Mage::getModel('core/resource_transaction')
                            ->addObject($invoice)
                            ->addObject($invoice->getOrder());
                        $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true, $message, true);
                        $order->save();
                        $transaction->save();
                        $invoice->sendEmail(true, '');

                        return true;
                    }
                }

                return false;
            }

            return "skip";
        } else {
            if ($transactionStatus == 'capture') {
                if ($fraudStatus == 'accept') {
                    return $this->processOrder($order, $paymentCode, $message);
                }
                elseif ($fraudStatus == 'challenge') {
                    $order->setState(Mage_Sales_Model_Order::STATE_NEW, 'cc_verification', $message, true);
                    $order->save();

                    return true;
                }
                elseif ($fraudStatus == 'deny') {
                    if ($order->canCancel()) {
                        $order->cancel();
                        $order->addStatusHistoryComment($message)
                            ->setIsCustomerNotified(true);
                    }

                    $order->save();

                    return true;
                }
                else {
                    // do nothing
                }
            }
            elseif ($transactionStatus == 'challenge') {
                $order->setState(Mage_Sales_Model_Order::STATE_NEW, 'cc_verification', $message, true);
                $order->save();

                return true;
            }
            elseif ($transactionStatus == 'deny') {
                if ($order->canCancel()) {
                    $order->cancel();
                    $order->addStatusHistoryComment($message)
                        ->setIsCustomerNotified(true);
                }

                $order->save();

                return true;
            }
            elseif ($transactionStatus == 'cancel') {
                if ($order->canCancel()) {
                    $order->cancel();
                    $order->addStatusHistoryComment($message)
                        ->setIsCustomerNotified(true);
                }

                $order->save();

                return true;
            }
            else {
                $order->addStatusHistoryComment('failed get response or timeout from Veritrans');
                $order->save();

                // write log to process confirmation
                $tdate = date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(time()));
                $content = sprintf("%s|%s", $order->getIncrementId() . " | Response charge is null", $tdate);

                return Mage::helper('paymethod')->writeLogFile($paymentCode, 'confirmation', $order->getIncrementId(), $content, 'normal');
            }
        }

        return false;
    }

    protected function processOrder($order, $paymentCode, $message) {
        if (! $order->canInvoice()) {
            Mage::log("Unable to create invoice for " . $order->getIncrementId(), Zend_Log::ERR);
            return false;
        }

        $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();
        if (! $invoice->getTotalQty()) {
            Mage::log("Invalid invoice for " . $order->getIncrementId(), Zend_Log::ERR);
            return false;
        }

        $invoice->register();
        $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true, $message, true);
        $transaction = Mage::getModel('core/resource_transaction')->addObject($invoice)->addObject($order);
        $transaction->save();

        $invoice->sendEmail(true, '');
        return true;
    }

    protected function isVtdirectNotification($notification, $paymentCode) {
        $paymentType = Mage::getStoreConfig('payment/' . $paymentCode . '/vtdirect_payment_type');

        if ($notification->payment_type == $paymentType) {
            return true;
        }

        return false;
    }

    public function addHistoryOrder($order, $charge) {
        $order->addStatusHistoryComment($charge->status_message);
        $order->save();
    }

    protected function getNotificationOrderStatusAllow() {
        $statusArr = array ('pending', 'pending_va');
        return $statusArr;
    }
}
