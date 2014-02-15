<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_SalesRule
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Discount calculation model
 *
 * @category    Mage
 * @package     Mage_SalesRule
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_SalesRule_Model_Quote_Discount extends Mage_Sales_Model_Quote_Address_Total_Abstract
{
    /**
     * Discount calculation object
     *
     * @var Mage_SalesRule_Model_Validator
     */
    protected $_calculator;

    /**
     * Initialize discount collector
     */
    public function __construct()
    {
        $this->setCode('discount');
        $this->_calculator = Mage::getSingleton('salesrule/validator');
    }

    private function _initRules($websiteId, $customerGroupId, $couponCode)
    {
        $key = $websiteId . '_' . $customerGroupId . '_' . $couponCode; 
        if (!isset($this->_rules[$key])) {
            $this->_rules[$key] = Mage::getResourceModel('salesrule/rule_collection')
                ->setValidationFilter($websiteId, $customerGroupId, $couponCode)
                ->load();
        }
    }
    /**
     * Collect address discount amount
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     * @return  Mage_SalesRule_Model_Quote_Discount
     */
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        parent::collect($address);
        $quote = $address->getQuote();
        $store = Mage::app()->getStore($quote->getStoreId());
        $this->_calculator->reset($address);

        $items = $this->_getAddressItems($address);
        if (!count($items)) {
            return $this;
        }
        $eventArgs = array(
            'website_id'        => $store->getWebsiteId(),
            'customer_group_id' => $quote->getCustomerGroupId(),
            'coupon_code'       => $quote->getCouponCode(),
        );

        $this->_calculator->init($store->getWebsiteId(), $quote->getCustomerGroupId(), $quote->getCouponCode());
        $this->_calculator->initTotals($items, $address);

        $address->setDiscountDescription(array());

        $key = $store->getWebsiteId() . '_' . $quote->getCustomerGroupId() . '_' . $quote->getCouponCode();
        $this->_initRules($store->getWebsiteId(), $quote->getCustomerGroupId(), $quote->getCouponCode());    
        $i=0;
        //store total discount for certain item(s) in a loop, and prorate it base on max amount
        $xItem = array();
        //store total discount that has been given to certain item(s)
        $discountItem = array();
        //define stop further processing rule
        $stopProcessing = array();
        
        foreach ($this->_rules[$key] as $rule) {
            //set max amount for current rule
            $maxAmount = $rule->getMaxAmount();
            //get total discount that have been given per rule
            $totalDiscount = $totalBaseDiscount = 0;
            $rid = $rule->getRuleId();
            foreach ($items as $item) {
                if($i==0){
                    $item->setDiscountAmount(0);
                    $item->setBaseDiscountAmount(0);
                    $item->setDiscountPercent(0);
                }

                if ($item->getNoDiscount()) {                
                    $item->setDiscountAmount(0);
                    $item->setBaseDiscountAmount(0);
                }
                else {
                    /**
                     * Child item discount we calculate for parent
                     */
                    if ($item->getParentItemId()) {
                        continue;
                    }
                    if (isset($stopProcessing[$item->getId()]) && $stopProcessing[$item->getId()]['value'] <> 0) {
                        if($stopProcessing[$item->getId()]['priority'] < $rule->getSortOrder()){
                            continue;
                        }
                    }

                    $eventArgs['item'] = $item;
                    Mage::dispatchEvent('sales_quote_address_discount_item', $eventArgs);

                    if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                        foreach ($item->getChildren() as $child) {
                            $this->_calculator->process($child, $rule);
                            $eventArgs['item'] = $child;
                            Mage::dispatchEvent('sales_quote_address_discount_item', $eventArgs);
                            //$this->_aggregateItemDiscount($child, $rule);
                        }
                    } else {
                        $this->_calculator->process($item, $rule, $discountItem);
                        //$this->_aggregateItemDiscount($item, $rule);
                    }
                    $totalDiscount += $item->getDiscountAmount();
                    $totalBaseDiscount += $item->getBaseDiscountAmount();
                    
                    $xItem[$item->getId()] = array(
                        'itemId'                => $item->getId(),
                        'discountAmount'        => $item->getDiscountAmount(),
                        'baseDiscountAmount'    => $item->getBaseDiscountAmount(),
                        'qty'                   => $this->_getItemQty($item, $rule)
                    );

                    if($item->getDiscountAmount() > 0){
                        $stopProcessing[$item->getId()] = array(
                                'priority'  => $rule->getSortOrder(),
                                'value'     => $rule->getStopRulesProcessing()
                        );
                    }
                }
            }

            /*start prorate mechanism*/
            foreach($xItem as $key => $val)
            {
                if($maxAmount > 0 && $totalDiscount > $maxAmount)
                {
                    $discountAmount = ($val['discountAmount']/$totalDiscount) * $maxAmount;
                    $baseDiscountAmount = ($val['baseDiscountAmount']/$totalDiscount) * $maxAmount;
                }else{
                    $discountAmount = $val['discountAmount'];
                    $baseDiscountAmount = $val['baseDiscountAmount'];
                }

                $xItem[$key] = array(
                    'itemId'             => $key,
                    'discountAmount'     => $discountAmount,
                    'baseDiscountAmount' => $baseDiscountAmount,
                    'qty'                => $this->_getItemQty($item, $rule)
                );
                    
                if(isset($discountItem[$key])){
                    $discountItem[$key]['discountAmount']       += $discountAmount;
                    $discountItem[$key]['baseDiscountAmount']   += $baseDiscountAmount;
                }else{
                    $discountItem[$key] = array(
                            'itemId'                => $key,
                            'discountAmount'        => $discountAmount,
                            'baseDiscountAmount'    => $baseDiscountAmount,
                    );
                }
            }
            /*end prorate mechanism*/

            //echo "<br/>rule $rid | totalDiscount ".$totalDiscount;
            $_rulesList[] = array(
                'rule_id'           => $rule->getRuleId(),
                'maxAmount'         => $rule->getMaxAmount(),
                'totalDiscount'     => $totalDiscount,
                'totalBaseDiscount' => $totalBaseDiscount,
                'xItem'             => $xItem
            );
            unset($xItem);
            
            $i++;
        }
/*echo '<pre>';        
print_r($_rulesList);
echo '</pre>';*/
        foreach ($items as $item) {
            $itemId = $item->getId();       
            $itemDiscount = 0;
            $itemBaseDiscount = 0;
            $itemDiscountBefore = 0;

            $baseItemPrice          = $this->_getItemBasePrice($item);

            foreach($_rulesList as $list)
            {
                if($itemId == $list['xItem'][$itemId]['itemId']){
                    $itemDiscount       += $list['xItem'][$itemId]['discountAmount'];
                    $itemBaseDiscount   += $list['xItem'][$itemId]['baseDiscountAmount'];
                }
/*echo "<br/> $itemId : ".$itemDiscount;
echo "<br/> $itemId base item price: ".$baseItemPrice;
echo "<br/> $itemId qty: ".$list['xItem'][$itemId]['qty'];  */              
                $qty = isset($list['xItem'][$itemId]['qty']) ? $list['xItem'][$itemId]['qty'] : 1;
                $xitemDiscount = min($itemDiscount, $baseItemPrice*$qty);
                $xitemBaseDiscount = min($itemBaseDiscount, $baseItemPrice*$qty); 
//print_r($list['xItem'][$itemId]);                               
            }
            
//echo $baseItemPrice*$list['xItem'][$itemId]['qty']." | ";
/*echo "<br/>x".$xitemDiscount;
echo "<br/>x".$xitemBaseDiscount;  */          

            $item->setDiscountAmount($xitemDiscount);
            $item->setBaseDiscountAmount($xitemBaseDiscount);
            $this->_aggregateItemDiscount($xitemDiscount, $xitemBaseDiscount);
        }

        /**
         * Process shipping amount discount
         */
        $address->setShippingDiscountAmount(0);
        $address->setBaseShippingDiscountAmount(0);
        if ($address->getShippingAmount()) {
            $this->_calculator->processShippingAmount($address);
            $this->_addAmount(-$address->getShippingDiscountAmount());
            $this->_addBaseAmount(-$address->getBaseShippingDiscountAmount());
        }

        $this->_calculator->prepareDescription($address);
        return $this;
    }

    /**
     * Aggregate item discount information to address data and related properties
     *
     * @param   Mage_Sales_Model_Quote_Item_Abstract $item
     * @return  Mage_SalesRule_Model_Quote_Discount
     */
    //protected function _aggregateItemDiscount($item, $rule)
    protected function _aggregateItemDiscount($discount, $baseDiscount)
    {
        $this->_addAmount(-$discount);
        $this->_addBaseAmount(-$baseDiscount);
        
        return $this;
    }

    /**
     * Add discount total information to address
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     * @return  Mage_SalesRule_Model_Quote_Discount
     */
    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        $amount = $address->getDiscountAmount();

        if ($amount != 0) {
            $description = $address->getDiscountDescription();
            if (strlen($description)) {
                $title = Mage::helper('sales')->__('Discount (%s)', $description);
            } else {
                $title = Mage::helper('sales')->__('Discount');
            }
            $address->addTotal(array(
                'code'  => $this->getCode(),
                'title' => $title,
                'value' => $amount
            ));
        }
        return $this;
    }

    /*nambahin neh*/
    /**
     * Return item base price
     *
     * @param Mage_Sales_Model_Quote_Item_Abstract $item
     * @return float
     */
    protected function _getItemBasePrice($item)
    {
        $price = $item->getDiscountCalculationPrice();
        return ($price !== null) ? $item->getBaseDiscountCalculationPrice() : $item->getBaseCalculationPrice();
    }

    /**
     * Return discount item qty
     *
     * @param Mage_Sales_Model_Quote_Item_Abstract $item
     * @param Mage_SalesRule_Model_Rule $rule
     * @return int
     */
    protected function _getItemQty($item, $rule)
    {
        $qty = $item->getTotalQty();
echo "<br/>qty :".$qty;
echo "<br/>discount qty :".$rule->getDiscountQty();        
        return $rule->getDiscountQty() ? min($qty, $rule->getDiscountQty()) : $qty;
    }
}
