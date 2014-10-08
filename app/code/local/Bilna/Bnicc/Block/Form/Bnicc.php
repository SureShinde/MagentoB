<?php
/**
 * Description of Bilna_Bnicc_Block_Form_Bnicc
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Bnicc_Block_Form_Bnicc extends Mage_Payment_Block_Form {
    protected $_code = 'bnicc';
    
    protected function _construct() {
        parent::_construct();
        
        $this->setTemplate($this->_code . '/form/' . $this->_code . '.phtml');
    }
    
    public function getDataBankIssuer() {
        return (string) Mage::getStoreConfig('payment/' . $this->_code . '/issuer');
    }
}
