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


class AW_Afptc_Block_Popup extends Mage_Catalog_Block_Product_Abstract
{
    protected function _construct()
    {
       if($this->helper('awafptc')->extensionDisabled()) {
           return;
       }
      
        $rule = $this->helper('awafptc')->getValidatedRule();
        if ($rule) {
            $rule = Mage::getModel('awafptc/rule')->load($rule);
            if ($rule->getId() && $rule->getProductId()) {
                $product = Mage::getModel('catalog/product')->load($rule->getProductId());
                if ($product->getId()) {
                    $this->setData('validated_rule', $rule);
                    $this->setData('related_product', $product);
                    $this->_prepareProductPrice();
                    $this->_prepareSessionKey();
                }
            }
        }
    }
    
    public function getDeclineCookieName()
    {
        return $this->helper('awafptc')->getDeclineCookieName($this->getValidatedRule()->getId());
    }
    
    protected function _toHtml()
    {       
       if (!$this->getProduct()) {
            return;
       }        
       if($this->cookieDisallowed()) {
           return;
       }
       
        return parent::_toHtml();
    }
    
    protected function cookieDisallowed()
    {        
        if (Mage::getSingleton('core/cookie')->get($this->getDeclineCookieName())) {          
            return $this->helper('awafptc')->unsetValidatedRule();
        }        
    }
    
    public function getQuoteId()
    {
        return Mage::getSingleton('checkout/cart')->getQuote()->getId();
    }

    public function getValidatedRule()
    {
        return $this->getData('validated_rule');
    }
    
    protected function _prepareSessionKey()
    {
        $this->helper('awafptc')->generateSessionKey($this->getData('validated_rule')->getId());
    }
    
    public function getSessionKey()
    {
        return $this->helper('awafptc')->getSessionKey();
    }
    
    public function getDoNotShowAllowed()
    {
        return (bool) $this->getValidatedRule()->getShowOnce();
    }
    
    public function getPostUrl()
    {
       return $this->getUrl('awafptc/cart/addProduct', array('form_key' =>  Mage::getSingleton('core/session')->getFormKey()));
    }
  
    protected function _prepareProductPrice()
    {
        $discount = $this->getValidatedRule()->getDiscount();        
        $finalPrice = $this->getProduct()->getFinalPrice();        
        $this->getProduct()->setFinalPrice(max(0, $finalPrice - ($finalPrice * $discount / 100)));
    }
    
    public function isAjax()
    {
        $request = Mage::app()->getRequest();
         
        if ($request->isXmlHttpRequest()) {
            return true;
        }
        if ($request->getParam('ajax') || $request->getParam('isAjax')) {
            return true;
        }
        return false;
    }

    public function getProduct()
    {
        return $this->getData('related_product');
    }
}