<?php
class Bilna_Customreports_Block_Info_Customreports extends Mage_Payment_Block_Info {
    protected function _construct() {
        parent::_construct();
        
        $this->setTemplate('customreports/info/anzcc.phtml');
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
