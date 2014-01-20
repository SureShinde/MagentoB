<?php
/**
 * Description of Bilna_Cod_Model_PaymentMethod
 *
 * @author  Bilna Development Team
 * @email   development@bilna.com  
 */

class Bilna_Cod_Model_PaymentMethod extends Mage_Payment_Model_Method_Abstract {
    protected $_code = 'cod';
    
    public function getSupportPaymentMethodsByShippingMethod($data) {
    	$allAvailablePaymentMethods = Mage::getModel('payment/config')->getActiveMethods();
    	$availablePayment = array();
    	foreach($allAvailablePaymentMethods as $key=>$payment){
    		$availablePayment[] = $key;
    	}
    	
        $readConn = Mage::getSingleton('core/resource')->getConnection('core_read');
        $shippingType = strtolower($data['shipping_type']);
        
        $sql  = "SELECT exclude_payment ";
        $sql .= "FROM payment_base_shipping ";
        $sql .= sprintf("WHERE id = '%s' ", $shippingType);
        $sql .= "ORDER BY exclude_payment ";
        
        $result = $readConn->fetchRow($sql);
        $exclude_payment = explode(",", $result["exclude_payment"]);
        
        foreach($exclude_payment as $payment){
        	$payment = trim($payment);
        	if(($key = array_search($payment, $availablePayment)) !== false) {
        		unset($availablePayment[$key]);
        	}
        }
        
        return $availablePayment;
    }
}
