<?php
/**
 * Description of Bilna_Fraud_Helper_Data
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Fraud_Helper_Data extends Mage_Core_Helper_Abstract {
    public function checkOrderStatus($orderId, $loadByIncrement = 0) {
        $canceled = 0;
        if($loadByIncrement == 1) {
            $orderData = Mage::getModel('sales/order')->loadByIncrementId($orderId);
        }
        else {
            $orderData = Mage::getModel('sales/order')->load($orderId);
        }

        $order = $orderData->getData('status');
        if(strtolower($order) == 'canceled') {
            $canceled = 1;
        }

        return $canceled;
    }
}
