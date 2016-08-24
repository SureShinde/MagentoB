<?php

/**
 * API2 class for checkout cart point (admin)
 *
 * @category   Bilna
 * @package    Bilna_Checkout
 * @author     Development Team <development@bilna.com>
 */

class Bilna_Checkout_Model_Api2_Point extends Bilna_Checkout_Model_Api2_Resource
{
    protected function _getCustomer($customerId)
    {
        /** @var $customer Mage_Customer_Model_Customer */
        $customer = Mage::getModel('customer/customer')
            ->load($customerId);
        if (!$customer->getId()) {
            $this->_critical('Customer Not Exists');
        }

        return $customer;
    }

    /**
     * Retrieves quote by quote identifier and store code or by quote identifier
     *
     * @param int $quoteId
     * @param string|int $store
     * @return Mage_Sales_Model_Quote
     */
    protected function _getQuote($quoteId, $storeId = 1)
    {
        /** @var $quote Mage_Sales_Model_Quote */
        $quote = Mage::getModel("sales/quote");

        if (!(is_string($storeId) || is_integer($storeId))) {
            $quote->loadByIdWithoutStore($quoteId);
        } else {
            $quote->setStoreId($storeId)
                    ->load($quoteId);
        }
        if (is_null($quote->getId())) {
            $this->_critical("Quote Not Exists");
        }

        return $quote;
    }
    
    protected function _getRules($quote, $customer)
    {
        $rules = [];
        
        if ($customer && $quote && $quote->getItemsCount() > 0) {
            $ruleCollection = Mage::getModel('points/rule')
                    ->getCollection()
                    ->addAvailableFilter()
                    ->addFilterByCustomerGroup($customer->getGroupId())
                    ->addFilterByWebsiteId(self::DEFAULT_STORE_ID)
                    ->setOrder('priority', Varien_Data_Collection::SORT_ORDER_ASC);

            foreach ($ruleCollection as $rule) {
                if ($rule->checkRule($quote)) {
                    $rules[] = $rule;
                    if ($rule->getStopRules())
                        break;
                }
            }
        }

        return $ruleCollection;
    }

    public function getPoints($quote, $customer)
    {
        $rules = $this->_getRules($quote, $customer);
        
        try {
            $pointsSummary = 0;
            $extraPointToBeAdd = 0;

            /* Ponts amount for the rules */
            foreach ($rules as $rule) {

                if ($rule->getApplyOn() == "cart_fixed") {
                        $extraPointToBeAdd = $rule->getPointsChange();
                } elseif ($rule->getApplyOn() == "by_fixed"){
                        $extraPointToBeAdd = (int)$quote->getItemsQty() * $rule->getPointsChange();
                } elseif ($rule->getApplyOn() == "by_percent"){
                        $extraPointToBeAdd = ($quote->getSubtotal() * $rule->getPointsChange()) / 100;
                } elseif ($rule->getApplyOn() == "by_percent_product" || $rule->getApplyOn() == "by_fixed_product") {
                    $extraPointToBeAdd = $rule->getPointByRule($quote);
                    if (!is_int($extraPointToBeAdd) and !is_float($extraPointToBeAdd)) {
                        $extraPointToBeAdd = $rule->getPointsChange();
                    }
                }

                if (((int)$rule->getMaxPointsChange() !== 0) && ((int)$rule->getMaxPointsChange() < (int)$extraPointToBeAdd)) {
                    $extraPointToBeAdd = $rule->getMaxPointsChange();
                }

                $pointsSummary += $extraPointToBeAdd;
            }

            $pointsSummary = $pointsSummary - ($pointsSummary % 500);

        } catch (Exception $ex) {
            $this->_critical($ex->getMessage());
        }

        return $pointsSummary;
    }

    public function getMoney($points, $customer)
    {
        $money = 0;

        try {
            $money = Mage::getModel('points/rate')->loadByDirection(AW_Points_Model_Rate::CURRENCY_TO_POINTS);
            $newAmount = (int) ((int)($points / $money->getMoney()) * $money->getPoints());
        } catch (Exception $ex) {
            $this->_critical($ex->getMessage());
        }

        return $newAmount;
    }
}
