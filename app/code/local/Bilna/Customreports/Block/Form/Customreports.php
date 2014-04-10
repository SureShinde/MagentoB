<?php
class Bilna_Customreports_Block_Form_Customreports extends Mage_Payment_Block_Form {
    protected function _construct() {
        parent::_construct();
        
        $this->setTemplate('customreports/form/anzcc.phtml');
    }
    
    public function getDataBankIssuer() {
        return (string) Mage::getStoreConfig('payment/customreports/issuer');
    }
}
