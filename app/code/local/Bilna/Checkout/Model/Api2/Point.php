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
                $rules[] = $rule;
                if ($rule->getStopRules())
                    break;
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
            $applyBefore = Mage::helper('points/config')->getPointsCollectionOrder() == AW_Points_Helper_Config::BEFORE_TAX;

            if ($applyBefore) {
                $apply = $quote->getData('base_subtotal_with_discount');
            } else {
                $baseSubtotal = $quote->getData('base_subtotal_with_discount');
                if ($quote->isVirtual()) {
                    $taxAmount = $quote->getBillingAddress()->getData('base_tax_amount');
                } else {
                    $taxAmount = $quote->getShippingAddress()->getData('base_tax_amount');
                }
                $apply = $baseSubtotal + $taxAmount;
            }

            //exchange code bellow are not working, so we use direct query to check point rate
            //$pointsSummary += Mage::getModel('points/rate')
            //        ->loadByDirection(AW_Points_Model_Rate::CURRENCY_TO_POINTS)
            //        ->exchange($apply);

            $_pointsSummary = $this->pointsRate([
                'direction' => AW_Points_Model_Rate::CURRENCY_TO_POINTS,
                'website_ids' => 1,
                'customer_group_ids' => $customer->getGroupId()
            ]);
            $_customersPoints = Mage::getModel('points/summary')->loadByCustomer($customer)->getPoints();
            $pointsSummary = (int) ((int)($apply / $_pointsSummary['money']) * $_pointsSummary['points']);

            if (Mage::helper('points/config')->getMaximumPointsPerCustomer()) {
                $customersPoints = 0;

                if ($customer) {
                    $customersPoints = Mage::getModel('points/summary')->loadByCustomer($customer)->getPoints();
                }

                if ($pointsSummary + $customersPoints > Mage::helper('points/config')->getMaximumPointsPerCustomer()) {
                    $pointsSummary = Mage::helper('points/config')->getMaximumPointsPerCustomer() - $customersPoints;
                }
            }

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

    private function pointsRate($param = []) {
        $resource = Mage::getSingleton('core/resource');

        /**
        * Retrieve the write connection
        */
        $writeConnection = $resource->getConnection('core_write');

        $tName = $resource->getTableName('points/rate');

        $query = "SELECT customer_group_ids, points, money FROM $tName WHERE direction = ".$param['direction']." AND website_ids = ".$param['website_ids']."";

        /**
        * Execute the query
        */
        $result = $writeConnection->fetchAll($query);

        if(!empty($result)) {
            foreach($result as $row) {
                $groups = explode(',', $row['customer_group_ids']);
                if(in_array($param['customer_group_ids'], $groups)) {
                    return $row;
                    break;
                }
            }
        }

        return FALSE;
    }
}