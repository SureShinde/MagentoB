<?php
/**
 * Description of Bilna_Visa_Block_Form_Visa
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Visa_Block_Form_Visa extends Mage_Payment_Block_Form {
    protected function _construct() {
        parent::_construct();
        
        $this->setTemplate('visa/form/visa.phtml');
    }
    
    public function getDataBankIssuer() {
        return (string) Mage::getStoreConfig('payment/visa/issuer');
    }
}
