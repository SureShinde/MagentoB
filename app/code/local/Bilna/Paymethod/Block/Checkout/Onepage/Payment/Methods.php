<?php
/**
 * Description of Bilna_Paymethod_Block_Checkout_Onepage_Payment_Methods
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Paymethod_Block_Checkout_Onepage_Payment_Methods extends Mage_Checkout_Block_Onepage_Payment_Methods {
    public function getPaymentMethodHidden() {
        $paymentHide = Mage::getStoreConfig('bilna_module/paymethod/payment_hide');
        $result = explode(',', $paymentHide);
        
        return $result;
    }
}
