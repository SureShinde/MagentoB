<?php

/**
 * @category    MP
 * @package     MP_MaxCouponDiscountAmount
 * @copyright   MagePhobia (http://www.magephobia.com)
 */
class MP_MaxCouponDiscountAmount_Model_SalesRule_Quote_Discount extends Mage_SalesRule_Model_Quote_Discount
{
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        Mage_Sales_Model_Quote_Address_Total_Abstract::collect($address);
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
        $version = Mage::getVersionInfo();
        if ($version['major'] == 1 && $version['minor'] == 9) {
            $items = $this->_calculator->sortItemsByPriority($items);
        }
        $maxDiscountAmount = $quote->getMaxDiscountAmount();

        $coupon = Mage::getModel('salesrule/coupon')->load($quote->getCouponCode(), 'code');
        $rule = Mage::getModel('salesrule/rule')->load($coupon->getRuleId());

        if ($maxDiscountAmount && $maxDiscountAmount != 0 && ($rule->getSimpleAction() == 'by_percent' || $rule->getSimpleAction() == 'by_fixed')) {
            $subTotals = $this->_getQuoteSubTotals($items, $rule->getActions(), $rule->getDiscountQty());
            $countItems = $this->_getQuoteItemsCount($items);
            if ($rule->getSimpleAction() == 'by_percent')
                $sumDiscount = $this->_getSumDiscount($items, $rule->getDiscountAmount(), $rule->getActions(), $rule->getDiscountQty());
            else
            if ($rule->getSimpleAction() == 'by_fixed')
                $sumDiscount = $this->_getSumDiscountByFixed($items, $rule->getDiscountAmount(), $rule->getActions(), $rule->getDiscountQty());
            $this->_calculator->setDataDiscount($maxDiscountAmount, $subTotals, $countItems, $sumDiscount);
        }
        $count = 1;
        foreach ($items as $item) {
            /*
            echo "----------------------------------<br />";
            echo "ITEM : " . $item->getName() . "<br />";
            */

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

                $eventArgs['item'] = $item;
                Mage::dispatchEvent('sales_quote_address_discount_item', $eventArgs);

                if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                    foreach ($item->getChildren() as $child) {
                        $this->_calculator->countForeach($count);
                        $this->_calculator->process($child);
                        $eventArgs['item'] = $child;
                        Mage::dispatchEvent('sales_quote_address_discount_item', $eventArgs);

                        $this->_aggregateItemDiscount($child);
                        $count++;
                    }
                } else {
                    $this->_calculator->countForeach($count);
                    $this->_calculator->process($item);
                    $this->_aggregateItemDiscount($item);
                    $count++;
                }
            }
        }

        /**
         * process weee amount
         */
        if (Mage::helper('weee')->isEnabled() && Mage::helper('weee')->isDiscounted($store)) {
            $this->_calculator->processWeeeAmount($address, $items);
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

    protected function _getQuoteSubTotals($items, $rule_actions, $discount_qty)
    {
        $totals = array();
        $parentItems = array();
        foreach ($items as $item) {
            if (!$item->getNoDiscount()) {
                // if the rule condition is not satisfied, do not process the below action
                if (!$rule_actions->validate($item)) {
                    continue;
                }
                // if the discount qty does not exist
                if (is_null($discount_qty) || $discount_qty == 0) {
                    $item_qty = $item->getQty();
                }
                else {
                    $item_qty = min($discount_qty, $item->getQty());
                }

                if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                    foreach ($item->getChildren() as $child) {
                        $totals[] = $child->getPrice() * $child->getQty() * $item_qty;
                        $parentItems[] = $item->getId();
                    }
                } else {
                    if (array_search($item->getParentItemId(), $parentItems) === false) {
                        $totals[] = $item->getPrice() * $item_qty;
                    }
                }
            }
        }

        $result = array_sum($totals);

        return $result;
    }


    protected function _getSumDiscount($items, $discountAmount, $rule_actions, $discount_qty)
    {
        // only total the items whose rules are validated
        $totals = array();
        $parentItems = array();
        foreach ($items as $item) {
            if (!$item->getNoDiscount()) {
                // if the rule condition is not satisfied, do not process the below action
                if (!$rule_actions->validate($item)) {
                    continue;
                }
                // if the discount qty does not exist
                if (is_null($discount_qty) || $discount_qty == 0) {
                    $item_qty = $item->getQty();
                }
                else {
                    $item_qty = min($discount_qty, $item->getQty());
                }

                if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                    foreach ($item->getChildren() as $child) {
                        $price = $child->getPrice() * $child->getQty() * $item_qty;
                        $percent = $discountAmount;
                        $discount = $price / 100 * $percent;
                        $totals[] = $discount;
                        $parentItems[] = $item->getId();
                    }
                } else {
                    if (array_search($item->getParentItemId(), $parentItems) === false) {
                        $price = $item->getPrice() * $item_qty;
                        $percent = $discountAmount;
                        $discount = $price / 100 * $percent;
                        $totals[] = $discount;
                    }
                }
            }
        }

        $result = array_sum($totals);

        return $result;
    }

    protected function _getSumDiscountByFixed($items, $discountAmount, $rule_actions, $discount_qty)
    {
        // only total the items whose rules are validated
        $totals = array();
        $parentItems = array();
        foreach ($items as $item) {
            if (!$item->getNoDiscount()) {
                // if the rule condition is not satisfied, do not process the below action
                if (!$rule_actions->validate($item)) {
                    continue;
                }
                // if the discount qty does not exist
                if (is_null($discount_qty) || $discount_qty == 0) {
                    $item_qty = $item->getQty();
                }
                else {
                    $item_qty = min($discount_qty, $item->getQty());
                }

                if ($item->getHasChildren())
                    continue;

                if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                    foreach ($item->getChildren() as $child) {
                        $price = $child->getPrice() * $child->getQty() * $item_qty;
                        $discount = $child->getQty() * $item_qty * $discountAmount;
                        $totals[] = $discount;
                        $parentItems[] = $item->getId();
                    }
                } else {
                    if (array_search($item->getParentItemId(), $parentItems) === false) {
                        $price = $item->getPrice() * $item_qty;
                        $discount = $item_qty * $discountAmount;
                        $totals[] = $discount;
                    }
                }
            }
        }

        $result = array_sum($totals);

        return $result;
    }

    protected function _getQuoteItemsCount($items)
    {
        $count = null;
        foreach ($items as $item) {
            if (!$item->getNoDiscount()) {
                if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                    continue;
                } else {
                    $count++;
                }
            }
        }

        return $count;
    }
}
