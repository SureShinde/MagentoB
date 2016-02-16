<?php
class Bilna_Customreports_Block_Installment extends Mage_Core_Block_Template {
    public function _getCustomreportsStatus() {
        if (Mage::getStoreConfig('payment/customreports/active')) {
            return true;
        }
        
        return false;
    }
    
    // public function _getPaymentMethod() {
        // if (Mage::getSingleton('checkout/session')->getQuote()->getPayment()->getMethodInstance()->getCode() == 'customreports') {
            // return true;
        // }
        
        // return false;
    // }
    
    public function _getInstallmentOptionPerOrder() {
        if (Mage::getStoreConfig('payment/customreports/installment_option') == 2) {
            return true;
        }
        
        return false;
    }
    
    public function _getInstallmentOption() {
        return unserialize(Mage::getStoreConfig('payment/customreports/installment'));
    }

    public function _getInstallmentFeature() {
        $totals = Mage::getSingleton('checkout/session')->getQuote()->getTotals(); //Total object
        $subTotal = round($totals['subtotal']->getValue()); //Subtotal value
        $grandTotal = round($totals['grand_total']->getValue()); //Grandtotal value
        $minOrderTotalInstallment = Mage::getStoreConfig('payment/customreports/min_order_total_installment');
        
        if ($grandTotal >= $minOrderTotalInstallment) {
            return true;
        }
        
        return false;
    }
}
