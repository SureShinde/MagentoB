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
     * Retrieve Name on card from Billing Address
     */
    public function getNameOnCard() {
        $fullname = '';
        
        if ($quote = Mage::getSingleton('checkout/session')->getQuote()) {
            $billingAddress = $quote->getBillingAddress();
            $firstname = $billingAddress->getData('firstname');
            $lastname = !empty ($billingAddress->getData('lastname')) ? $billingAddress->getData('lastname') : '';
            $fullname = sprintf("%s %s", $firstname, $lastname);
        }
        
        return $fullname;
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
    
    /**
     * 
     * @return boolean
     * true => zip code ok
     * false => zip code is null
     */
    public function checkZipCode() {
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $billingAddress = $quote->getBillingAddress();
        $zipCode = $billingAddress->getPostcode();
        
        /**
         * check customer is guest
         */
        //if (Mage::getSingleton('customer/session')->getId()) {
        //    if (empty ($zipCode)) {
        //        $customerAddressId = Mage::getSingleton('customer/session')->getCustomer()->getDefaultBilling();
        //
        //        if ($customerAddressId) {
        //            $address = Mage::getModel('customer/address')->load($customerAddressId);
        //            $zipCode = $address->getData('postcode');
        //        }
        //    }
        //}
        
        if (!empty ($zipCode)) {
            return true;
        }
        
        return false;
    }
}
