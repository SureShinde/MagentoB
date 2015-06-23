<?php
class Bilna_Scbcc_Block_Form_Scbcc extends Mage_Payment_Block_Form {
    protected function _construct() {
        parent::_construct();
        
        $this->setTemplate('scbcc/form/scbcc.phtml');
    }
    
    public function getDataBankIssuer() {
        return (string) Mage::getStoreConfig('payment/scbcc/issuer');
    }
}
