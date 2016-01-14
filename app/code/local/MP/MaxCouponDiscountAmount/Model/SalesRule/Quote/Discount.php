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
        $items = $this->_calculator->sortItemsByPriority($items);
        $maxDiscountAmount = $quote->getMaxDiscountAmount();

        if ($maxDiscountAmount && $maxDiscountAmount != 0) {
            $subTotals = $this->_getQuoteSubTotals($items);
            $countItems = $this->_getQuoteItemsCount($items);
            $sumDiscount = $this->_getSumDiscount($items);
            $this->_calculator->setDataDiscount($maxDiscountAmount, $subTotals, $countItems, $sumDiscount);
        }
        $count = 1;
        foreach ($items as $item) {
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

    protected function _getQuoteSubTotals($items)
    {
        $totals = array();
        foreach ($items as $item) {
            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                continue;
            } else {
                $totals[] = $item->getPrice() * $item->getQty();
            }
        }

        $result = array_sum($totals);

        return $result;
    }


    protected function _getSumDiscount($items)
    {
        $totals = array();
        foreach ($items as $item) {
            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                continue;
            } else {
                $price = $item->getPrice() * $item->getQty();
                $percent = $item->getDiscountPercent();
                $discount = $price / 100 * $percent;
                $totals[] = $discount;
            }
        }

        $result = array_sum($totals);

        return $result;
    }

    protected function _getQuoteItemsCount($items)
    {
        $count = null;
        foreach ($items as $item) {
            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                continue;
            } else {
                $count++;
            }
        }

        return $count;
    }
}
