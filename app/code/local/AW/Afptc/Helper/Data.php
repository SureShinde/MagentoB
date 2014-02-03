<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento community edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento community edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Afptc
 * @version    1.0.0
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Afptc_Helper_Data extends Mage_Core_Helper_Abstract
{     
    const POPUP_PRODUCT_RULE = 'aw-afptc-session-rule';
    
    const AW_AFPTC_RULE_DECLINE = 'aw-afptc-rule-decline';
     
    public function getCustomerGroup()
    {        
        return $this->_session()->isLoggedIn() ? $this->_session()->getCustomer()->getGroupId() : 0;
    }
    
    public function getCustomerId()
    {
        return $this->_session()->getCustomer()->getId();
    }
    
    public function getValidatedRule()
    {
        return $this->_session()->getAwAfptcRule();    
    }
    
    public function getDeclineCookieName($rule)
    {
        return self::AW_AFPTC_RULE_DECLINE . '-' . $rule;  // . '-' . $quote;
    }
    
    public function unsetValidatedRule()
    {  
        return $this->_session()->unsAwAfptcRule();  
    }
    
    public function setValidatedRule($rule)
    {        
        return $this->_session()->setAwAfptcRule($rule);    
    } 
 
    public function quoteChanged($observer, $cart)
    {       
        return $this->getQuoteStateCache($cart) != $observer->getQuoteState($cart->getQuote());
    }
    
    public function getQuoteStateCache($cart)
    {     
       if(!$this->getCustomerId()) {            
           return $this->_session()->getData("quote_state_{$cart->getQuote()->getId()}");       
       }
       return Mage::getResourceModel('awafptc/state')->getState(array(
            'customer_id' => $this->getCustomerId(),
            'quote_id' => $cart->getQuote()->getId()
        ));         
    }
    
    public function setQuoteState($observer, $cart)
    {
        if(!$this->getCustomerId()) {            
           return $this->_session()->setData("quote_state_{$cart->getQuote()->getId()}", 
                    $observer->getQuoteState($cart->getQuote()));
        } 
        
        Mage::getResourceModel('awafptc/state')->setState(array(
            'customer_id' => $this->getCustomerId(),
            'quote_id' => $cart->getQuote()->getId(),
            'state'    => $observer->getQuoteState($cart->getQuote())
        ));        
    }
 
    public function generateSessionKey($rule)
    {
        $enc = $this->encrypt(array('key' => time() . mt_rand(), 'rule' => $rule));
       
        $this->_session()->setAwAfptcSessionRule($enc['rule']);
        
        $this->_session()->setAwAfptcSessionKey($enc['key']);
    }
    
    public function getSessionKey()
    {
       return $this->_session()->getAwAfptcSessionKey();
    }
    
    public function validateSessionKey($key)
    {
        return $key === $this->_session()->getAwAfptcSessionKey();
    }
    
    public function getRuleFromSession()
    {
        $rule = $this->_session()->getAwAfptcSessionRule();
        
        $this->_session()->unsAwAfptcSessionRule();
       
        if (!$rule) {
            return;
        }

        $dec = $this->decrypt(array('rule' => $rule));

        $ruleObj = Mage::getModel('awafptc/rule')->load($dec['rule']);
        
        if ($ruleObj->getId()) {
            return $ruleObj;
        }
    }
    
    public function encrypt(array $data)
    {
        foreach ($data as $key => &$value) {
            if (substr($key, 0, 1) == '_') {
                continue;
            }
            $value = $this->urlEncode(Mage::helper('core')->encrypt($value));
        }

        return $data;
    }

    public function decrypt(array $data)
    {
        foreach ($data as $key => &$value) {
            if (substr($key, 0, 1) == '_') {
                continue;
            }
            $value = Mage::helper('core')->decrypt($this->urlDecode($value));
        }

        return $data;
    }
    
    public function extensionDisabled()
    {        
        return Mage::getStoreConfig('advanced/modules_disable_output/AW_Afptc') ||
                !Mage::getStoreConfig('awafptc/general/enable');
    }

    protected function _session()
    {
        return Mage::getSingleton('customer/session');
    }

}
