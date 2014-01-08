<?php
/**
 * Description of Bilna_Paymethod_Block_Vtdirect_Processing
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Paymethod_Block_Vtdirect_Processing extends Mage_Core_Block_Template {
    public function getOrderId() {
        return Mage::helper('paymethod/vtdirect')->getOrderId();
    }
    
    public function getOrder() {
        return Mage::getModel('sales/order')->load($this->getOrderId());
    }
    
    public function getTokenId() {
        //Mage::getSingleton('core/session')->setVtdirectTokenIdCreate(date('yyyy-mm-dd H:i:s'));
        //Mage::getSingleton('core/session')->setVtdirectTokenId($data['token_id']);
        
        return Mage::getSingleton('core/session')->getVtdirectTokenId();
    }
    
    public function maxChar($text, $maxLength = 10) {
        return substr($text, 0, $maxLength);
    }
    
    public function removeSymbols($text) {
        return Mage::helper('paymethod/vtdirect')->removeSymbols($text);
    }
}
