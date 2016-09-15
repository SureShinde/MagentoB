<?php
/**
 * API2 class for Afptc (admin)
 *
 * @category   Bilna
 * @package    Custom AW_Afptc 
 * @author     Development Team <development@bilna.com>
 */

class AW_Afptc_Model_Api2_Observer_Rest_Admin_V1 extends AW_Afptc_Model_Api2_Observer_Rest {
    //- Default store Id (for install)
    const DISTRO_STORE_ID = 1;
    
    //- Default store code (for install)
    const DISTRO_STORE_CODE = 'default';

    protected $baseSubtotalFree = 0;
    protected $itemWeightFree = 0;
    protected $_quoteRules = [];
    
    protected function _retrieve() {
        try {
            $quoteId = $this->getRequest()->getParam('id');
            $quote = $this->_getQuote($quoteId);
            $customerId = $quote->getCustomerId();
            $customerGroup = 0;
            
            if ($customerId != null) {
                $customer = Mage::getModel('customer/customer')->load($customerId);
                //$customerGroup = Mage::getModel('customer/group')->load($customer->getGroupId());
                $groupId = $customer->getGroupId();
            } else {
                $groupId = 0;
            }

            $helper = Mage::helper('awafptc');
            $this->excludeFreeProductsFrom($quote);
            $items  = $this->_getItems(array($quoteId));
            
            if (!empty($items)) {
                
                $rules = Mage::getModel('awafptc/rule')->getActiveRules([
                    'store' => self::DISTRO_STORE_ID,
                    'group' => $groupId,
                    'website' => 1
                ]);

                $activeRules = [];
                $popupRules = [];
                
                foreach ($rules as $rule) {
                    //- rules deleted by customers are ignored
                    if ($this->isRuleDeleted($rule, $quote)) {
                        continue;
                    }

                    //- avoide multiple validations of rules with popups
                    if ($rule->getShowPoup() && $helper->getValidatedRule() && !in_array($rule->getId(), $this->_quoteRules)) {
                        continue;
                    }

                    $this->_prepareValidate($quote);
                    $cart = Mage::getModel('checkout/cart')->setQuote($quote);
                    
                    if (!$rule->load($rule->getId())->validate($cart)) {
                        continue;
                    }

                    //- register valid rule for poup rules for later usage
                    if ($rule->getShowPopup() && !in_array($rule->getId(), $this->_quoteRules)) {
                        //- bug fix config data always empty, since , change from $rule to $rule->getData()
                        array_push($popupRules, $rule->getData());
                        continue;
                    }
                    
                    array_push($activeRules, $rule);
                }

                foreach ($activeRules as $rule) {
                    $product = Mage::getModel('catalog/product')->load($rule['product_id']);
                    
                    if (!$product->getId()) {
                        continue;
                    }
                    
                    try {
                        $quote->addProduct(
                            $product->setData('aw_afptc_rule', $rule['rule_id'])
                                ->addCustomOption('aw_afptc_discount', min(100, $rule['discount']))
                                ->addCustomOption('aw_afptc_rule', $rule['rule_id'])
                        )->setQty(1);
                    }
                    catch (Exception $e) {
                        throw Mage::throwException($e->getMessage());
                    }
                }
            }

            $quote->unsTotalsCollectedFlag()->collectTotals()->save();
            
            return ['config' => $popupRules];
        }
        catch (Mage_Core_Exception $e) {
            $this->_critical($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        }
    }

    /**
     * @param  $rule [type]
     * @param  $quote [type]
     * @return boolean
     */
    public function isRuleDeleted($rule, $quote) {
        $deletedRules = $this->__getDeletedRules($quote);
        
        if ($deletedRules) {
            foreach ($deletedRules as $delRule) {
                if ($delRule->getRuleId() == $rule->getId()) {
                    if ($delRule->getIsRemoved()) {
                        return true;
                    }
                }
            }
        }
        
        return false;
    }

    private function __getDeletedRules($quote) {
        return Mage::getModel('awafptc/used')->loadDeletedRules($quote->getId());
    }

    public function excludeFreeProductsFrom($quote) {
        $subtotal = null;
        $weight = null;
       
        foreach ($quote->getAllVisibleItems() as $item) {
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
    
    protected function _prepareValidate($quote) {
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

    /**
     * Retrieve a list or orders' items in a form of [order ID => array of items, ...]
     *
     * @param array $orderIds Orders identifiers
     * @return array
     */
    protected function _getItems(array $orderIds) {
        $items = array ();

        if ($this->_isSubCallAllowed('quote_item')) {
            /** @var $itemsFilter Mage_Api2_Model_Acl_Filter */
            $itemsFilter = $this->_getSubModel('quote_item', array ())->getFilter();
            
            // do items request if at least one attribute allowed
            if ($itemsFilter->getAllowedAttributes()) {
                $resource = Mage::getSingleton('core/resource');
                $adapter = $resource->getConnection('core_read');
                $tableName = $resource->getTableName('sales_flat_quote_item');
                $select = $adapter->select()
                    ->from(array ('sfqi' => $tableName,new Zend_Db_Expr('*')))
                    ->joinLeft(
                        array ('sfqio' => $resource->getTableName('sales_flat_quote_item_option')),
                        'sfqi.item_id=sfqio.item_id',
                        array ('code' => 'sfqio.code', 'value' => 'sfqio.value', 'option_id'=> 'sfqio.option_id')
                    )
                    ->where('quote_id IN ('.implode(",",array_values($orderIds)).')');
                
                $salesQuotesItem = $adapter->fetchAll($select);
                
                foreach ($salesQuotesItem as $quoteItem) {
                    $options[$quoteItem['item_id']]['options'][$quoteItem['option_id']]['code'] = $quoteItem['code'];
                    $options[$quoteItem['item_id']]['options'][$quoteItem['option_id']]['value'] = $quoteItem['value'];
                    $options[$quoteItem['item_id']]['quote'] = $quoteItem;
                }

                foreach ($options as $qi) {
                    $qi['quote']['item_options'] = $qi['options'];
                    $items[$qi['quote']['quote_id']][] = $itemsFilter->out($qi['quote']);
                }
            }
        }
        
        return $items;
    }
}
