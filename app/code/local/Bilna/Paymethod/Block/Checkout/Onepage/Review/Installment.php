<?php
/**
 * Description of Bilna_Paymethod_Block_Klikpay_Installment
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Paymethod_Block_Checkout_Onepage_Review_Installment extends Mage_Core_Block_Template {
    public function getPaymentMethod() {
        return Mage::getSingleton('checkout/session')->getQuote()->getPayment()->getMethodInstance()->getCode();
    }
    
    public function getPaymentMethodStatus() {
        if (Mage::getStoreConfig('payment/' . $this->getPaymentMethod() . '/active')) {
            return true;
        }
        
        return false;
    }
    
    public function getInstallmentSupport() {
        return Mage::getStoreConfig('payment/' . $this->getPaymentMethod() . '/allow_installment');
    }
    
    public function getInstallmentMethod() {
        return Mage::getStoreConfig('payment/' . $this->getPaymentMethod() . '/installment_process');
    }
    
    public function getInstallmentOptionPerOrder() {
        if (Mage::getStoreConfig('payment/' . $this->getPaymentMethod() . '/installment_option') == 2) {
            return true;
        }
        
        return false;
    }

    public function getInstallmentOption() {
        return unserialize(Mage::getStoreConfig('payment/' . $this->getPaymentMethod() . '/installment'));
    }
    
    public function getInstallmentFeature() {
        $totals = Mage::getSingleton('checkout/session')->getQuote()->getTotals(); //Total object
        $subTotal = round($totals['subtotal']->getValue()); //Subtotal value
        $grandTotal = round($totals['grand_total']->getValue()); //Grandtotal value
        $minOrderTotalInstallment = Mage::getStoreConfig('payment/' . $this->getPaymentMethod() . '/min_installment_total');
        $maxOrderTotalInstallment = Mage::getStoreConfig('payment/' . $this->getPaymentMethod() . '/max_installment_total');
        $minOrderTotalCheck = false;
        $maxOrderTotalCheck = false;
        
        // check minimum order total
        if (empty ($minOrderTotalInstallment) || $minOrderTotalInstallment == '') {
            $minOrderTotalCheck = true;
        }
        else {
            if ($grandTotal >= $minOrderTotalInstallment) {
                $minOrderTotalCheck = true;
            }
        }
        
        // check maximum order total
        if (empty ($maxOrderTotalInstallment) || $maxOrderTotalInstallment == '') {
            $maxOrderTotalCheck = true;
        }
        else {
            if ($grandTotal <= $maxOrderTotalInstallment) {
                $maxOrderTotalCheck = true;
            }
        }
        
        if ($minOrderTotalCheck && $maxOrderTotalCheck) {
            return true;
        }
        
        return false;
    }
    
    public function getMinInstallmentTotal() {
        return Mage::getStoreConfig('payment/' . $this->getPaymentMethod() . '/min_installment_total');
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    public function _getPaymentMethodStatus() {
        if (Mage::getStoreConfig('payment/klikpay/active')) {
            return true;
        }
        
        return false;
    }
    
    public function _getPaymentMethod() {
        if (Mage::getSingleton('checkout/session')->getQuote()->getPayment()->getMethodInstance()->getCode() == 'klikpay') {
            return true;
        }
        
        return false;
    }
    
    public function _getInstallmentOptionPerOrder() {
        if (Mage::getStoreConfig('payment/klikpay/installment_option') == 2) {
            return true;
        }
        
        return false;
    }
    
    public function _getInstallmentOption() {
        return unserialize(Mage::getStoreConfig('payment/klikpay/installment'));
    }


    //public function _getInstallmentFeature() {
    //    $totals = Mage::getSingleton('checkout/session')->getQuote()->getTotals(); //Total object
    //    $subTotal = round($totals['subtotal']->getValue()); //Subtotal value
    //    $grandTotal = round($totals['grand_total']->getValue()); //Grandtotal value
    //    $minOrderTotalInstallment = Mage::getStoreConfig('payment/klikpay/min_order_total_installment');
    //    
    //    if ($grandTotal >= $minOrderTotalInstallment) {
    //        return true;
    //    }
    //    
    //    return false;
    //}
}
