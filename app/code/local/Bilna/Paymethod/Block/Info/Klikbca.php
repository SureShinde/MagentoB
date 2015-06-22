<?php
/**
 * Description of Bilna_Paymethod_Block_Info_Klikbca
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Paymethod_Block_Info_Klikbca extends Mage_Payment_Block_Info {
    protected function _construct() {
        parent::_construct();
        $this->setTemplate('paymethod/info/klikbca.phtml');
    }
    
    public function toPdf() {
        $this->setTemplate('paymethod/info/pdf/klikbca.phtml');
        return $this->toHtml();
    }
    
    protected function _prepareSpecificInformation($transport = null) {
        if (null !== $this->_paymentSpecificInformation) {
            return $this->_paymentSpecificInformation;
        }
        
        $info = $this->getInfo();
        $transport = new Varien_Object();
        $transport = parent::_prepareSpecificInformation($transport);
        $transport->addData(array (
            Mage::helper('payment')->__('KlikBCA User Id:') => $info->getKlikbcaUserId()
        ));
        
        return $transport;
    }
}
