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
    protected $_isGateway = true;
 
    /**
     * Can authorize online?
     */
    protected $_canAuthorize = true;
 
    /**
     * Can capture funds online?
     */
    protected $_canCapture = true;
 
    /**
     * Can capture partial amounts online?
     */
    protected $_canCapturePartial = false;
 
    /**
     * Can refund online?
     */
    protected $_canRefund = false;
 
    /**
     * Can void transactions online?
     */
    protected $_canVoid = true;
 
    /**
     * Can use this payment method in administration panel?
     */
    protected $_canUseInternal = true;
 
    /**
     * Can show this payment method as an option on checkout payment page?
     */
    protected $_canUseCheckout = true;
 
    /**
     * Is this payment method suitable for multi-shipping checkout?
     */
    protected $_canUseForMultishipping = false;
 
    /**
     * Can save credit card information for future processing?
     */
    protected $_canSaveCc = false;
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
    
    public function validate() {
        parent::validate();

        $info = $this->getInfoInstance();
        $errorMsg = false;
        $availableTypes = explode(',',$this->getConfigData('cctypes'));

        $ccNumber = $info->getCcNumber();

        // remove credit card number delimiters such as "-" and space
        $ccNumber = preg_replace('/[\-\s]+/', '', $ccNumber);
        $info->setCcNumber($ccNumber);

        $ccType = '';

        if (in_array($info->getCcType(), $availableTypes)) {
            if ($this->validateCcNum($ccNumber) || ($this->OtherCcType($info->getCcType()) && $this->validateCcNumOther($ccNumber))) {
                $ccType = 'OT';
                $ccTypeRegExpList = array (
                    //Solo, Switch or Maestro. International safe
                    /*
                    // Maestro / Solo
                    'SS'  => '/^((6759[0-9]{12})|(6334|6767[0-9]{12})|(6334|6767[0-9]{14,15})'
                               . '|(5018|5020|5038|6304|6759|6761|6763[0-9]{12,19})|(49[013][1356][0-9]{12})'
                               . '|(633[34][0-9]{12})|(633110[0-9]{10})|(564182[0-9]{10}))([0-9]{2,3})?$/',
                    */
                    // Solo only
                    'SO' => '/(^(6334)[5-9](\d{11}$|\d{13,14}$))|(^(6767)(\d{12}$|\d{14,15}$))/',
                    'SM' => '/(^(5[0678])\d{11,18}$)|(^(6[^05])\d{11,18}$)|(^(601)[^1]\d{9,16}$)|(^(6011)\d{9,11}$)'
                            . '|(^(6011)\d{13,16}$)|(^(65)\d{11,13}$)|(^(65)\d{15,18}$)'
                            . '|(^(49030)[2-9](\d{10}$|\d{12,13}$))|(^(49033)[5-9](\d{10}$|\d{12,13}$))'
                            . '|(^(49110)[1-2](\d{10}$|\d{12,13}$))|(^(49117)[4-9](\d{10}$|\d{12,13}$))'
                            . '|(^(49118)[0-2](\d{10}$|\d{12,13}$))|(^(4936)(\d{12}$|\d{14,15}$))/',
                    // Visa
                    'VI'  => '/^4[0-9]{12}([0-9]{3})?$/',
                    // Master Card
                    'MC'  => '/^5[1-5][0-9]{14}$/',
                    // American Express
                    'AE'  => '/^3[47][0-9]{13}$/',
                    // Discovery
                    'DI'  => '/^6011[0-9]{12}$/',
                    // JCB
                    'JCB' => '/^(3[0-9]{15}|(2131|1800)[0-9]{11})$/'
                );

                foreach ($ccTypeRegExpList as $ccTypeMatch=>$ccTypeRegExp) {
                    if (preg_match($ccTypeRegExp, $ccNumber)) {
                        $ccType = $ccTypeMatch;
                        break;
                    }
                }

                if (!$this->OtherCcType($info->getCcType()) && $ccType != $info->getCcType()) {
                    $errorMsg = Mage::helper('payment')->__('Credit card number mismatch with credit card type.');
                }
            }
            else {
                $errorMsg = Mage::helper('payment')->__('Invalid Credit Card Number');
            }
        }
        else {
            $errorMsg = Mage::helper('payment')->__('Credit card type is not allowed for this payment method.');
        }

        //validate credit card verification number
        if ($errorMsg === false && $this->hasVerification()) {
            $verifcationRegEx = $this->getVerificationRegEx();
            $regExp = isset ($verifcationRegEx[$info->getCcType()]) ? $verifcationRegEx[$info->getCcType()] : '';
            
            if (!$info->getCcCid() || !$regExp || !preg_match($regExp ,$info->getCcCid())) {
                $errorMsg = Mage::helper('payment')->__('Please enter a valid credit card verification number.');
            }
        }

        if ($ccType != 'SS' && !$this->_validateExpDate($info->getCcExpYear(), $info->getCcExpMonth())) {
            $errorMsg = Mage::helper('payment')->__('Incorrect credit card expiration date.');
        }

        if ($errorMsg) {
            Mage::throwException($errorMsg);
        }

        //This must be after all validation conditions
        if ($this->getIsCentinelValidationEnabled()) {
            $this->getCentinelValidator()->validate($this->getCentinelValidationData());
        }

        return $this;
    }
}
