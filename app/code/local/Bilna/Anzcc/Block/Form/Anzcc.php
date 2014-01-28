<?php
class Bilna_Anzcc_Block_Form_Anzcc extends Mage_Payment_Block_Form {
    protected function _construct() {
        parent::_construct();
        
        $this->setTemplate('anzcc/form/anzcc.phtml');
    }
    
    public function getDataBankIssuer() {
        return (string) Mage::getStoreConfig('payment/anzcc/issuer');
    }
}
