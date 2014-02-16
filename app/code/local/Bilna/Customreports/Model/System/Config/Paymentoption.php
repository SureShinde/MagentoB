<?php
class Bilna_Customreports_Model_System_Config_Paymentoption {
    public function toOptionArray() {
        $allAvailablePaymentMethods = Mage::getModel('payment/config')->getAllMethods();
        $methods = array ();
        
        foreach ($allAvailablePaymentMethods as $paymentCode => $paymentModel) {
            $paymentTitle = Mage::getStoreConfig('payment/' . $paymentCode . '/title');
            $methods[$paymentCode] = array (
                'label' => $paymentTitle,
                'value' => $paymentCode,
            );
        }
        
        return $methods;
    }
}