<?php
/**
 * Description of Bilna_Paymethod_Block_Klikpay_Pay
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Paymethod_Block_Klikpay_Pay extends Bilna_Paymethod_Block_Checkout_Onepage_Redirect_Klikpay {
    protected function _getOrder() {
        if ($this->getRequest()->getParam('id')) {
            return Mage::getModel('sales/order')->loadByIncrementId($this->getRequest()->getParam('id'));
        }
        else {
            return null;
        }
    }
}
