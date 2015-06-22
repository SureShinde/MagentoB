<?php
class Bilna_Anzcc_Block_Info_Anzcc extends Mage_Payment_Block_Info {
    protected function _construct() {
        parent::_construct();
        
        $this->setTemplate('anzcc/info/anzcc.phtml');
    }
    
    protected function _prepareSpecificInformation($transport = null) {
        if (null !== $this->_paymentSpecificInformation) {
            return $this->_paymentSpecificInformation;
        }
        
        $info = $this->getInfo();
        $transport = new Varien_Object();
        $transport = parent::_prepareSpecificInformation($transport);
        $transport->addData(array (
            Mage::helper('payment')->__('ANZ Bin Number:') => $info->getCcBins()
        ));
        
        return $transport;
    }
}
