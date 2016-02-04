<?php
/**
 * Description of Bilna_Bnicc_Block_Info_Bnicc
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Bnicc_Block_Info_Bnicc extends Mage_Payment_Block_Info {
    protected $_code = 'bnicc';
    
    protected function _construct() {
        parent::_construct();
        
        $this->setTemplate($this->_code . '/info/' . $this->_code . '.phtml');
    }
    
    protected function _prepareSpecificInformation($transport = null) {
        if (null !== $this->_paymentSpecificInformation) {
            return $this->_paymentSpecificInformation;
        }
        
        $info = $this->getInfo();
        $transport = new Varien_Object();
        $transport = parent::_prepareSpecificInformation($transport);
        $transport->addData(array (
            Mage::helper('payment')->__('BNI Bin Number:') => $info->getCcBins()
        ));
        
        return $transport;
    }
}
