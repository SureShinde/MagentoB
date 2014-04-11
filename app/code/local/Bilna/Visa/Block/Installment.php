<?php
/**
 * Description of Bilna_Visa_Block_Installment
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Visa_Block_Installment extends Mage_Core_Block_Template {
    public function _getScbccStatus() {
        if (Mage::getStoreConfig('payment/visa/active')) {
            return true;
        }
        
        return false;
    }
    
    public function _getPaymentMethod() {
        if (Mage::getSingleton('checkout/session')->getQuote()->getPayment()->getMethodInstance()->getCode() == 'visa') {
            return true;
        }
        
        return false;
    }
    
    public function _getInstallmentOptionPerOrder() {
        if (Mage::getStoreConfig('payment/visa/installment_option') == 2) {
            return true;
        }
        
        return false;
    }
    
    public function _getInstallmentOption() {
        return unserialize(Mage::getStoreConfig('payment/visa/installment'));
    }

    public function _getInstallmentFeature() {
        $totals = Mage::getSingleton('checkout/session')->getQuote()->getTotals(); //Total object
        $subTotal = round($totals['subtotal']->getValue()); //Subtotal value
        $grandTotal = round($totals['grand_total']->getValue()); //Grandtotal value
        $minOrderTotalInstallment = Mage::getStoreConfig('payment/visa/min_order_total_installment');
        
        if ($grandTotal >= $minOrderTotalInstallment) {
            return true;
        }
        
        return false;
    }
}
