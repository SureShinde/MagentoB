<?php
class Bilna_Scbcc_Helper_Data extends Mage_Core_Helper_Abstract {
    public function getPaymentmentOptionLabel($value) {
        $paymentOptions = Mage::getModel('scbcc/system_config_paymentoption')->toOptionArray();
        
        foreach ($paymentOptions as $_option) {
            if ($_option['value'] == $value) {
                return $_option['label'];
            }
        }
        
        return;        
    }
    
    public function getInstallmentOptionLabel($value) {
        $installmentOptions = unserialize(Mage::getStoreConfig('payment/scbcc/installment'));
        
        foreach ($installmentOptions as $_option) {
            if ($_option['value'] == $value) {
                return $_option['label'];
            }
        }
        
        return;        
    }
    
    public function getInstallmentOption($id, $returnKey) {
        $installmentOptions = unserialize(Mage::getStoreConfig('payment/scbcc/installment'));
        
        foreach ($installmentOptions as $_option) {
            if ($_option['id'] == $id) {
                return $_option[$returnKey];
            }
        }
        
        return;        
    }
    
    public function getInstallmentOptionCollection() {
        return unserialize(Mage::getStoreConfig('payment/scbcc/installment'));
    }
}
