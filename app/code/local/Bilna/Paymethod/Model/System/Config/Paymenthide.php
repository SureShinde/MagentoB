<?php
/**
 * Description of Bilna_Paymethod_Model_System_Config_Paymenthide
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Paymethod_Model_System_Config_Paymenthide {
    public function toOptionArray() {
        $payments = Mage::getSingleton('payment/config')->getActiveMethods();
        $methods = array ();
        
        foreach ($payments as $paymentCode => $paymentModel) {
            $paymentTitle = Mage::getStoreConfig('payment/' . $paymentCode . '/title');
            $methods[$paymentCode] = array (
                'label' => $paymentTitle,
                'value' => $paymentCode,
            );
        }
        
        return $methods;
    }
}
