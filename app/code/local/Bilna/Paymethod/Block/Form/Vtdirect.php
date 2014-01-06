<?php
/**
 * Description of Bilna_Paymethod_Block_Form_Vtdirect
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Paymethod_Block_Form_Vtdirect extends Mage_Payment_Block_Form_Ccsave {
    protected function _construct() {
        parent::_construct();
        
        $this->setTemplate('paymethod/form/vtdirect.phtml');
    }
    
    public function getBaseUrl() {
        return Mage::getBaseUrl();
    }
    
    public function getClientKey() {
        return Mage::getStoreConfig('payment/vtdirect/client_key');
    }
}
