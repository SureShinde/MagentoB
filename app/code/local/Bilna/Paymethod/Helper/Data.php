<?php
/**
 * Description of Bilna_Paymethod_Helper_Data
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Paymethod_Helper_Data extends Mage_Core_Helper_Abstract {
    public function getInstallmentOption($paymentMethod, $id, $returnKey = 'label') {
        $installmentOptions = unserialize(Mage::getStoreConfig('payment/' . $paymentMethod . '/installment'));
        
        foreach ($installmentOptions as $_option) {
            if ($_option['id'] == $id) {
                return $_option[$returnKey];
            }
        }
        
        return;
    }
}
