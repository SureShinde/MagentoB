<?php
/**
 * Description of Bilna_Paymethod_Block_Checkout_Onepage_Success
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Paymethod_Block_Checkout_Onepage_Success extends Mage_Checkout_Block_Onepage_Success {
    public function getInstruction() {
        $orderId = $this->getOrderId();
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
        $paymentCode = $order->getPayment()->getMethodInstance()->getCode();
        $instruction = Mage::getStoreConfig('payment/' . $paymentCode . '/instructions');
        
        if (empty ($instruction)) {
            $instruction = Mage::getStoreConfig('payment/' . $paymentCode . '/message');
        }
        
        return $instruction;
    }
}
