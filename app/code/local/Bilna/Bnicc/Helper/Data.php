<?php
/**
 * Description of Bilna_Bnicc_Helper_Data
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Bnicc_Helper_Data extends Mage_Core_Helper_Abstract {
    protected $_code = 'bnicc';
    
    public function getPaymentmentOptionLabel($value) {
        $paymentOptions = Mage::getModel($this->_code . '/system_config_paymentoption')->toOptionArray();
        
        foreach ($paymentOptions as $_option) {
            if ($_option['value'] == $value) {
                return $_option['label'];
            }
        }
        
        return;        
    }
    
    public function getInstallmentOptionLabel($value) {
        $installmentOptions = unserialize(Mage::getStoreConfig('payment/' . $this->_code . '/installment'));
        
        foreach ($installmentOptions as $_option) {
            if ($_option['value'] == $value) {
                return $_option['label'];
            }
        }
        
        return;        
    }
    
    public function getInstallmentOption($id, $returnKey) {
        $installmentOptions = unserialize(Mage::getStoreConfig('payment/' . $this->_code . '/installment'));
        
        foreach ($installmentOptions as $_option) {
            if ($_option['id'] == $id) {
                return $_option[$returnKey];
            }
        }
        
        return;        
    }
    
    public function getInstallmentOptionCollection() {
        return unserialize(Mage::getStoreConfig('payment/' . $this->_code . '/installment'));
    }
}
