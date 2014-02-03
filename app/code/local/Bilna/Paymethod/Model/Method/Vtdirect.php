<?php
/**
 * Description of Bilna_Paymethod_Model_Method_Vtdirect
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Paymethod_Model_Method_Vtdirect extends Mage_Payment_Model_Method_Cc {
    /**
    * unique internal payment method identifier
    *
    * @var string [a-z0-9_]
    */
    protected $_code = 'vtdirect';
 
    /**
     * Here are examples of flags that will determine functionality availability
     * of this module to be used by frontend and backend.
     *
     * @see all flags and their defaults in Mage_Payment_Model_Method_Abstract
     *
     * It is possible to have a custom dynamic logic by overloading
     * public function can* for each flag respectively
     */
     
    /**
     * Is this payment method a gateway (online auth/charge) ?
     */
    //protected $_isGateway = true;
 
    /**
     * Can authorize online?
     */
    protected $_canAuthorize = true;
 
    /**
     * Can capture funds online?
     */
    //protected $_canCapture = true;
 
    /**
     * Can capture partial amounts online?
     */
    //protected $_canCapturePartial = false;
 
    /**
     * Can refund online?
     */
    //protected $_canRefund = true;
 
    /**
     * Can void transactions online?
     */
    //protected $_canVoid = true;
 
    /**
     * Can use this payment method in administration panel?
     */
    //protected $_canUseInternal = true;
 
    /**
     * Can show this payment method as an option on checkout payment page?
     */
    //protected $_canUseCheckout = true;
 
    /**
     * Is this payment method suitable for multi-shipping checkout?
     */
    //protected $_canUseForMultishipping = false;
 
    /**
     * Can save credit card information for future processing?
     */
    //protected $_canSaveCc = false;
    protected $_formBlockType = 'paymethod/form_vtdirect';
    protected $_infoBlockType = 'paymethod/info_vtdirect';
 
    /**
     * Here you will need to implement authorize, capture and void public methods
     *
     * @see examples of transaction specific public methods such as
     * authorize, capture and void in Mage_Paygate_Model_Authorizenet
     */
    public function authorize(Varien_Object $payment, $amount) {
        if (!$this->canAuthorize()) {
            Mage::throwException(Mage::helper('payment')->__('Authorize action is not available.'));
        }
        
        /**
         * check token expire
         */
        $tokenExpiredTime = Mage::getStoreConfig('payment/vtdirect/token_expired');
        $tokenCreated = Mage::getSingleton("core/session")->getVtdirectTokenIdCreate();
        $now = date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(time()));
        $tokenInterval = strtotime($now) - strtotime($tokenCreated);
        
        if ($tokenInterval > $tokenExpiredTime) {
            Mage::throwException(Mage::helper('paymethod')->__('token was expired'));
        }
        
        return $this;
    }

    public function getOrderPlaceRedirectUrl() {
        return Mage::getUrl('paymethod/vtdirect/thankyou', array ('_secure' => true));
    }
    
    public function getCode() {
        if (empty ($this->_code)) {
            Mage::throwException(Mage::helper('payment')->__('Cannot retrieve the payment method code.'));
        }
        
        return $this->_code;
    }
    
    public function getBankCode($cardNo) {
        $read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $binCode = substr($cardNo, 0, 6);
        
        $sql = "SELECT issuer ";
        $sql .= "FROM bin_code ";
        $sql .= sprintf("WHERE code = '%s' ", $binCode);
        $sql .= "LIMIT 1 ";
        
        return $read->fetchOne($sql);
    }
}
