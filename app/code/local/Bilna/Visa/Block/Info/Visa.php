<?php
/**
 * Description of Bilna_Visa_Block_Info_Visa
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Visa_Block_Info_Visa extends Mage_Payment_Block_Info {
    protected function _construct() {
        parent::_construct();
        
        $this->setTemplate('visa/info/visa.phtml');
    }
    
    protected function _prepareSpecificInformation($transport = null) {
        if (null !== $this->_paymentSpecificInformation) {
            return $this->_paymentSpecificInformation;
        }
        
        $info = $this->getInfo();
        $transport = new Varien_Object();
        $transport = parent::_prepareSpecificInformation($transport);
        $transport->addData(array (
            Mage::helper('payment')->__('Visa Bin Number:') => $info->getCcBins()
        ));
        
        return $transport;
    }
}
