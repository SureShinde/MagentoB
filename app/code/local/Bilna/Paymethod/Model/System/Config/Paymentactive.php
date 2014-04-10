<?php
/**
 * Description of Bilna_Paymethod_Model_System_Config_Paymentactive
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Paymethod_Model_System_Config_Paymentactive {
    public function toOptionArray() {
        //$payments = Mage::getSingleton('payment/config')->getAllMethods();
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
