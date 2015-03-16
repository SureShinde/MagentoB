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


class AW_Afptc_Model_Observer extends Varien_Object
{ 
    protected $_quoteRules = array();    
    
    protected function _construct()
    {
        $this->setHelper(Mage::helper('awafptc'));
        
        $this->setStopOutput($this->getHelper()->extensionDisabled()); 
    }
    
    public function checkoutCartSaveAfter($observer)
    {     
        if ($this->getStopOutput())
            return;

        $helper = $this->getHelper();

        $cart = $observer->getCart();

        if (!$helper->quoteChanged($this, $cart)) {
            return;
        }

        $helper->unsetValidatedRule();

        $this->excludeFreeProductsFrom($cart);

        if ($cart->getQuote()->hasItems()) {
            $rules = Mage::getModel('awafptc/rule')->getActiveRules(array(
                'store' => Mage::app()->getStore()->getId(),
                'group' => $helper->getCustomerGroup(),
                'website' => Mage::app()->getStore()->getWebsite()->getId()
            ));            
            $activeRules = array();
            foreach ($rules as $rule) {
                /* rules deleted by customers are ignored */
                if ($this->isRuleDeleted($rule, $cart)) {
                    continue;
                }
                /* avoide multiple validations of rules with popups */
                if ($rule->getShowPoup() && $helper->getValidatedRule() && !in_array($rule->getId(), $this->_quoteRules)) {
                    continue;
                }               
               
                $this->_prepareValidate($cart);
                if (!$rule->load($rule->getId())->validate($cart)) {
                    continue;
                }
                /* register valid rule for poup rules for later usage */
                if ($rule->getShowPopup() && !in_array($rule->getId(), $this->_quoteRules)) {
                    if (!$helper->getValidatedRule()) {                        
                        $helper->setValidatedRule($rule->getId());
                    }
                    continue;
                }
                array_push($activeRules, $rule);
            }
           
            foreach ($activeRules as $rule) {
                $product = Mage::getModel('catalog/product')->load($rule->getProductId());
                if (!$product->getId())
                    continue;
                try {
                    $cart->getQuote()->addProduct($product->setData('aw_afptc_rule', $rule))->setQty(1);                
                    Mage::app()->getResponse()->setRedirect(
                            Mage::getUrl('checkout/cart/index', Mage::app()->getRequest()->getParams()));
                } catch (Exception $e) {
                    Mage::logException($e);
                }
            }
        }

        $cart->getQuote()->unsTotalsCollectedFlag()->collectTotals()->save();

        $helper->setQuoteState($this, $cart);
    }
    
    
    protected function _prepareValidate($cart)
    {        
        $cart->setData('all_items', $cart->getQuote()->getAllItems());
         
        if ($cart->getQuote()->isVirtual()) {
            $address = $cart->getQuote()->getBillingAddress();
        }
        else {
            $address = $cart->getQuote()->getShippingAddress();
        }
       
        $address->setTotalQty($cart->getItemsQty());
       
        $address->setBaseSubtotal($address->getBaseSubtotal() - $this->getData('base_subtotal_free'));   
        
        $address->setWeight($address->getWeight() - $this->getData('item_weight_free')); 
    }
    
    public function isRuleDeleted($rule, $cart)
    {        
        $cookie = Mage::helper('awafptc')->getDeclineCookieName($rule->getId());
        
        if(Mage::getSingleton('core/cookie')->get($cookie)) {
            return true;
        }
        
        if(!$this->getDeletedRules()) {             
            $this->setDeletedRules(Mage::getModel('awafptc/used')->loadDeletedRules($cart->getQuote()->getId()));
        }    
        
        foreach($this->getDeletedRules() as $delRule) {            
            if($delRule->getRuleId() == $rule->getId()) {
                if($delRule->getIsRemoved()) {
                     return true;
                }                   
            }            
        }     
    }
    
    public function salesQuoteRemoveAfter($observer)
    {
        if($this->getStopOutput() || $this->hasFakeRemove()) {
            return;
        }
        $option = $observer->getQuoteItem()->getOptionByCode('info_buyRequest');
        $buyRequest = new Varien_Object($option && $option->getValue() ? unserialize($option->getValue()) : null);
        if ($buyRequest->getAwAfptcRule()) {
           Mage::getResourceModel('awafptc/used')->markAsDeleted($buyRequest->getAwAfptcRule(), 
                   $observer->getQuoteItem()->getQuote()->getId());       
        }
    }
   
    public function excludeFreeProductsFrom($cart)
    {
       if($this->getStopOutput())
            return;
        
       $this->setFakeRemove(true); 
       $subtotal = null;
       $weight = null;
       foreach($cart->getQuote()->getAllVisibleItems() as $item) { 
            $option = $item->getProduct()->getCustomOption('aw_afptc_rule');
            if ($option) {
                array_push($this->_quoteRules, $option->getValue());  
                $subtotal += $item->getBaseRowTotal();
                $weight += $item->getWeight();                
                $cart->removeItem($item->getId());               
            }           
       }
        
       $this->setData('base_subtotal_free', $subtotal)
            ->setData('item_weight_free', $weight);
       
       $this->unsFakeRemove(true);
       
      $cart->getQuote()->unsTotalsCollectedFlag()->collectTotals();  
    }

    public function getQuoteState($quote)
    {
        if($this->getStopOutput())
            return;
        
        $state = $quote->getBaseSubtotal();
        foreach ($quote->getAllVisibleItems() as $item) {
            $state .= "-{$item->getQty()}";
        }

        return $state;
    }

    public function prepareCart($observer)
    {
        if ($this->getStopOutput())
            return;
        if (!$rule = $observer->getProduct()->getAwAfptcRule()) {
            $rules = Mage::registry(AW_Afptc_Helper_Data::POPUP_PRODUCT_RULE);
            if (!is_array($rules) || !isset($rules[$observer->getProduct()->getId()])) {
                return;
            }
            $rule = $rules[$observer->getProduct()->getId()];
        }
        if (!$rule instanceof Varien_Object) {
            return;
        }
        $observer->getProduct()->addCustomOption('aw_afptc_discount', min(100, $rule->getDiscount()));
        $observer->getProduct()->addCustomOption('aw_afptc_rule', $rule->getId());
        $observer->getBuyRequest()->setData('aw_afptc_rule', $rule->getId());
    }

    public function getFinalPrice($observer)
    {
        if($this->getStopOutput())
            return;
        
        $option = $observer->getProduct()->getCustomOption('aw_afptc_discount');
        if ($option) {
            $finalPrice = $observer->getProduct()->getFinalPrice();
            $observer->getProduct()->setFinalPrice(max(0, $finalPrice - ($finalPrice * $option->getValue() / 100)));
        }
    }

    /**
     * added for compatibility with AW_ACP
     * @see AFPTC-5
     * @param $observer
     */
    public function checkoutCartUpdateItemsAfter($observer)
    {
        $cart = $observer->getCart();
        foreach($cart->getQuote()->getAllVisibleItems() as $item) {
            $option = $item->getOptionByCode('info_buyRequest');
            $buyRequest = new Varien_Object($option && $option->getValue() ? unserialize($option->getValue()) : null);
            if ($buyRequest->getAwAfptcRule()) {
                if($item->getQty() > 1) {
                    throw new Mage_Core_Exception(
                        $this->getHelper()->__(
                            "Unfortunately the quantity of %s can not be changed due to current set of products in the cart",
                            $item->getProduct()->getName()
                        )
                    );
                }
            }
        }
    }
}