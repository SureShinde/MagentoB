<?php
/**
 * Description of Bilna_Paymethod_Block_Info_Klikpay
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Paymethod_Block_Info_Klikpay extends Mage_Payment_Block_Info {
    protected function _construct() {
        parent::_construct();
        $this->setTemplate('paymethod/info/klikpay.phtml');
    }
    
    public function toPdf() {
        $this->setTemplate('paymethod/info/pdf/klikpay.phtml');
        return $this->toHtml();
    }
    
    protected function _prepareSpecificInformation($transport = null) {
        if (null !== $this->_paymentSpecificInformation) {
            return $this->_paymentSpecificInformation;
        }
        
        $info = $this->getInfo();
        $transport = new Varien_Object();
        $transport = parent::_prepareSpecificInformation($transport);
        
        return $transport;
    }
}
