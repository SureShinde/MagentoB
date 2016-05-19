<?php
/**
 * Description of Bilna_Sales_Model_Api2_Order_Rest
 * 
 * @path   app/code/local/Bilna/Sales/Model/Api2/Order/Rest.php
 * @author Bilna Development Team <development@bilna.com>
 */

abstract class Bina_Sales_Model_Api2_Order_Rest extends Bilna_Sales_Model_Api2_Order {
    protected function _getPaymentTitle($order) {
        try {
            if ($paymentTitle = $order->getPayment()->getMethodInstance()->getTitle()) {
                return $paymentTitle;
            }
            
            return '';
        }
        catch (Exception $ex) {
            return '';
        }
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
