<?php
/**
 * Description of Bilna_Vtdirect_Form_Vtdirect
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Vtdirect_Block_Form_Vtdirect extends Mage_Payment_Block_Form_Ccsave {
    protected function _construct() {
        parent::_construct();
        
        $this->setTemplate('vtdirect/form/vtdirect.phtml');
    }
    
    public function getClientKey() {
        return Mage::getStoreConfig('payment/vtdirect/client_key');
    }
}
