<?php
/**
 * Description of Bilna_Paymethod_Model_Observer
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Paymethod_Model_Observer extends Varien_Event_Observer {
    public function salesOrderLog($observer) {
        if (Mage::helper('paymethod')->salesOrderLogActive()) {
            $order = $observer->getEvent()->getOrder();
            $dataArr = array (
                'orderId' => $order->getId(),
                'incrementId' => $order->getIncrementId(),
                'customerEmail' => $order->getCustomerEmail(),
                'paymentMethod' => $order->getPayment()->getMethodInstance()->getCode(),
                'status' => $order->getStatus(),
                'subtotal' => $order->getSubtotal(),
                'grandTotal' => $order->getGrandTotal(),
                'createdAt' => $order->getCreatedAt(),
                'updatedAt' => $order->getUpdatedAt(),
            );
            $message = sprintf("%s => %s" , $order->getIncrementId(), json_encode($dataArr));
            Mage::helper('paymethod')->salesOrderLog($message);
        }
    }
}
