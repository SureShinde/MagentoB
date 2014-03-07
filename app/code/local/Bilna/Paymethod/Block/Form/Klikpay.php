<?php
/**
 * Description of Bilna_Paymethod_Block_Form_Klikpay
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Paymethod_Block_Form_Klikpay extends Mage_Payment_Block_Form {
    protected function _construct() {
        parent::_construct();
        
        $this->setTemplate('paymethod/form/klikpay.phtml');
    }
}

