<?php
/**
 * Description of Bilna_Paymethod_Block_Form_Vtdirect
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Paymethod_Block_Form_Vtdirect extends Mage_Payment_Block_Form_Ccsave {
    protected function _construct() {
        parent::_construct();
        
        $this->setTemplate('paymethod/form/vtdirect.phtml');
    }
    
    /**
     * Retrieve credit card expire months
     *
     * @return array
     */
    public function getCcMonths() {
        $months = $this->getData('cc_months');
        if (is_null($months)) {
            $months[0] =  $this->__('Month');
            $months = array_merge($months, $this->_getConfig()->getMonths());
            $this->setData('cc_months', $months);
        }
        return $months;
    }
    
    public function getClientKey() {
        return Mage::getStoreConfig('payment/vtdirect/client_key');
    }
}
