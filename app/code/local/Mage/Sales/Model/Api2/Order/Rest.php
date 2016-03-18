<?php
/**
 * Description of Bilna_Rest_Model_Api2_Category_Rest
 * 
 * @path   app/code/local/Mage/Sales/Model/Api2/Order/Rest.php
 * @author Bilna Development Team <development@bilna.com>
 */

abstract class Mage_Sales_Model_Api2_Order_Rest extends Mage_Sales_Model_Api2_Order {
    protected function _getPaymentTitle($order) {
        if ($paymentTitle = $order->getPayment()->getMethodInstance()->getTitle()) {
            return $paymentTitle;
        }
        
        return null;
    }

    protected function _getAdditionalInfo($order) {
        $result = array ();
        
        //- get virtual account number
        if ($vaNumber = $order->getPayment()->getVaNumber()) {
            $result['va_number'] = $vaNumber;
        }
        
        return $result;
    }
}
