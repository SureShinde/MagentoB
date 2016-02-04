<?php
/**
 * Description of Bilna_Bnicc_Block_Installment
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Bnicc_Block_Installment extends Mage_Core_Block_Template {
    protected $_code = 'bnicc';
    
    public function _getBniccStatus() {
        if (Mage::getStoreConfig('payment/' . $this->_code . '/active')) {
            return true;
        }
        
        return false;
    }
    
    public function _getPaymentMethod() {
        if (Mage::getSingleton('checkout/session')->getQuote()->getPayment()->getMethodInstance()->getCode() == $this->_code) {
            return true;
        }
        
        return false;
    }
    
    public function _getInstallmentOptionPerOrder() {
        if (Mage::getStoreConfig('payment/' . $this->_code . '/installment_option') == 2) {
            return true;
        }
        
        return false;
    }
    
    public function _getInstallmentOption() {
        return unserialize(Mage::getStoreConfig('payment/' . $this->_code . '/installment'));
    }

    public function _getInstallmentFeature() {
        $totals = Mage::getSingleton('checkout/session')->getQuote()->getTotals(); //Total object
        $subTotal = round($totals['subtotal']->getValue()); //Subtotal value
        $grandTotal = round($totals['grand_total']->getValue()); //Grandtotal value
        $minOrderTotalInstallment = Mage::getStoreConfig('payment/' . $this->_code . '/min_order_total_installment');
        
        if ($grandTotal >= $minOrderTotalInstallment) {
            return true;
        }
        
        return false;
    }
}
