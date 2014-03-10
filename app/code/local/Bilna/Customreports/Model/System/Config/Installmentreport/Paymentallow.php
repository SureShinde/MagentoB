<?php
/**
 * Description of Bilna_Customreports_Model_System_Config_Installmentreport_Paymentallow
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Customreports_Model_System_Config_Installmentreport_Paymentallow {
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
