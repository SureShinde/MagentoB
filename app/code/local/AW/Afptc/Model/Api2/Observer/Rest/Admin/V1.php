<?php

/**
 * API2 class for Afptc (admin)
 *
 * @category   Bilna
 * @package    Custom AW_Afptc 
 * @author     Development Team <development@bilna.com>
 */
class AW_Afptc_Model_Api2_Observer_Rest_Admin_V1 extends AW_Afptc_Model_Api2_Observer_Rest
{
    /**
     * Default store Id (for install)
     */
    const DISTRO_STORE_ID       = 1;
    
    /**
     * Default store code (for install)
     *
     */
    const DISTRO_STORE_CODE     = 'default';

    protected $baseSubtotalFree = 0;
    protected $itemWeightFree = 0;
    protected $_quoteRules = array();
    
    protected function _retrieve()
    {
        $quoteId = $this->getRequest()->getParam('id');

        try{

            $quote = $this->_getQuote($quoteId);
            $customerId = $quote->getCustomerId();
            
            $customerGroup = 0;
            if($customerId != null)
            {
                $customer = Mage::getModel('customer/customer')->load($customerId);
                $customerGroup = Mage::getModel('customer/group')->load($customer->getGroupId()); 
            }

            $helper = Mage::helper('awafptc');
            $this->excludeFreeProductsFrom($quote);

            if($quote->hasItems())
            {
                $rules = Mage::getModel('awafptc/rule')->getActiveRules(array(
                        'store' => self::DISTRO_STORE_ID,
                        'group' => $customerGroup,
                        'website' => 1
                ));

                $activeRules = array();
                $popupRules = array();
                foreach ($rules as $rule)
                {
                    /* rules deleted by customers are ignored */
                    if ($this->isRuleDeleted($rule, $quote)) {
                        continue;
                    }

                    /* avoide multiple validations of rules with popups */
                    if ($rule->getShowPoup() && $helper->getValidatedRule() && !in_array($rule->getId(), $this->_quoteRules)) {
                        continue;
                    }

                    $this->_prepareValidate($quote);
                    $cart = Mage::getModel('checkout/cart')->setQuote($quote);
                    if (!$rule->load($rule->getId())->validate($cart)) {
                        continue;
                    }

                    /* register valid rule for poup rules for later usage */
                    if ($rule->getShowPopup() && !in_array($rule->getId(), $this->_quoteRules)) {
                        /*if (!$helper->getValidatedRule()) {                        
                            $helper->setValidatedRule($rule->getId());
                        }*/
                        array_push($popupRules, $rule->getData()); //bug fix config data always empty, since , change from $rule to $rule->getData()
                        continue;
                    }
                    array_push($activeRules, $rule->getData());
                }

                foreach ($activeRules as $rule)
                {
                    $product = Mage::getModel('catalog/product')->load($rule->getProductId());
                    if (!$product->getId())
                        continue;
                    try {
                        $quote->addProduct($product->setData('aw_afptc_rule', $rule))->setQty(1);
                    } catch (Exception $e) {
                        throw Mage::throwException($e->getMessage());
                    }
                }

            }

            $quote->unsTotalsCollectedFlag()->collectTotals()->save();

        } catch (Mage_Core_Exception $e) {
                $this->_error($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        }

        return array('config' => $popupRules);

    }

    /**
     * @param  $rule [type]
     * @param  $quote [type]
     * @return boolean
     */
    public function isRuleDeleted($rule, $quote)
    {        
        $deletedRules = $this->__getDeletedRules($quote);
        if($deletedRules)
        {
            foreach($deletedRules as $delRule)
            {
                if($delRule->getRuleId() == $rule->getId())
                {
                    if($delRule->getIsRemoved()) {
                         return true;
                    }
                }
            }
        }
        return false;
    }

    private function __getDeletedRules($quote)
    {
        return Mage::getModel('awafptc/used')->loadDeletedRules($quote->getId());
    }

    public function excludeFreeProductsFrom($quote)
    {
       $subtotal = null;
       $weight = null;
       foreach($quote->getAllVisibleItems() as $item)
       {
            $option = $item->getProduct()->getCustomOption('aw_afptc_rule');
            if ($option) {
                array_push($this->_quoteRules, $option->getValue());  
                $subtotal += $item->getBaseRowTotal();
                $weight += $item->getWeight();                
                $quote->removeItem($item->getId());
            }
       }

       $this->baseSubtotalFree = $subtotal;
       $this->itemWeightFree = $weight;

       $quote->unsTotalsCollectedFlag()->collectTotals();  

    }

    protected function _prepareValidate($quote)
    {         
        if ($quote->isVirtual()) {
            $address = $quote->getBillingAddress();
        }
        else {
            $address = $quote->getShippingAddress();
        }
       
        $address->setTotalQty($quote->getItemsQty());
       
        $address->setBaseSubtotal($address->getBaseSubtotal() - $this->baseSubtotalFree);   
        
        $address->setWeight($address->getWeight() - $this->itemWeightFree); 
    }

}