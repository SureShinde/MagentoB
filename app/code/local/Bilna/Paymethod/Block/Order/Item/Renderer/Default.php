<?php
/**
 * Description of Bilna_Paymethod_Block_Order_Item_Renderer_Default
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Paymethod_Block_Order_Item_Renderer_Default extends Mage_Sales_Block_Order_Item_Renderer_Default {
    public function getPaymentMethodCode() {
        return $this->getOrder()->getPayment()->getMethodInstance()->getCode();
    }
    
    public function getKlikpayActive() {
        return Mage::getStoreConfig('payment/klikpay/active');
    }
    
    public function getKlikpayInstallmentOption() {
        return Mage::getStoreConfig('payment/klikpay/installment_option');
    }
    
    public function getKlikpayInstallments() {
        return Mage::getStoreConfig('payment/klikpay/installment');
    }
    
    public function getKlikpayMinOrderTotalInstallment() {
        return Mage::getStoreConfig('payment/klikpay/min_order_total_installment');
    }
    
    public function getKlikpayInstallmentLabel($installmentId) {
        return $this->helper('paymethod')->getInstallmentOption($this->getPaymentMethodCode(), $installmentId);
    }
}
