<?php
class Bilna_Scbcc_Block_Info_Scbcc extends Mage_Payment_Block_Info {
    protected function _construct() {
        parent::_construct();
        
        $this->setTemplate('scbcc/info/scbcc.phtml');
    }
    
    protected function _prepareSpecificInformation($transport = null) {
        if (null !== $this->_paymentSpecificInformation) {
            return $this->_paymentSpecificInformation;
        }
        
        $info = $this->getInfo();
        $transport = new Varien_Object();
        $transport = parent::_prepareSpecificInformation($transport);
        $transport->addData(array (
            Mage::helper('payment')->__('Standard Chartered Bin Number:') => $info->getCcBins()
        ));
        
        return $transport;
    }
}
