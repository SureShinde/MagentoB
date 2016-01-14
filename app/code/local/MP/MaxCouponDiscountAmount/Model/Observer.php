<?php
/**
 * @category    MP
 * @package     MP_MaxCouponDiscountAmount
 * @copyright   MagePhobia (http://www.magephobia.com)
 */

class MP_MaxCouponDiscountAmount_Model_Observer
{
    public function salesQuoteCollectTotalsAfter(Varien_Event_Observer $observer)
    {
        $quote = $observer->getEvent()->getQuote();
        $couponCode = $quote->getCouponCode();
        $checkoutSession = Mage::getSingleton('checkout/session');
        if ($couponCode) {

            if (($quote->getMaxDiscountAmount() == 0) && !$checkoutSession->getDoNotCheckForMaxDiscountAmount()) {
                $couponMaxDiscountAmount = Mage::getResourceModel('mp_maxcoupondiscountamount/promocode')
                    ->getCouponMaxDiscountAmount($couponCode);
                if ($couponCode && ($quote->getMaxDiscountAmount() == 0) && ($couponMaxDiscountAmount > 0)) {
                    if (($couponMaxDiscountAmount > 0) && ($quote->getMaxDiscountAmount() != $couponMaxDiscountAmount)) {
                        $quote->setMaxDiscountAmount((float) $couponMaxDiscountAmount)->save();
                        $checkoutSession->setDoNotCheckForMaxDiscountAmount(true);
                    }
                } elseif ($couponMaxDiscountAmount == 0) {
                    $checkoutSession->setDoNotCheckForMaxDiscountAmount(true);
                }
            }

            $discountAmount = (float)($quote->getSubtotal() - $quote->getSubtotalWithDiscount());
            $tax = $quote->getShippingAddress()->getData('tax_amount');
            if (($quote->getMaxDiscountAmount() > 0) && ($discountAmount > $quote->getMaxDiscountAmount())) {
                $quote->setSubtotalWithDiscount((float)($quote->getSubtotal() - $quote->getMaxDiscountAmount()));
                $quote->setBaseSubtotalWithDiscount((float)($quote->getSubtotal() * $quote->getStoreToQuoteRate()
                    - $quote->getMaxDiscountAmount() * $quote->getStoreToQuoteRate()));
                $quote->setGrandTotal((float)($quote->getSubtotal() - $quote->getMaxDiscountAmount())+$tax);
                $quote->setBaseGrandTotal((float)($quote->getSubtotal() * $quote->getStoreToQuoteRate()
                    - $quote->getMaxDiscountAmount() * $quote->getStoreToQuoteRate()) +$tax);

                $quote->save();
                if (!$checkoutSession->getDoNotShowMaxDiscountAmountNotice()) {
                    Mage::getSingleton('core/session')->addNotice('Maximum discount amount of '
                        . Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol()
                        . number_format($quote->getMaxDiscountAmount(), 2) . ' reached.');
                    $checkoutSession->setDoNotShowMaxDiscountAmountNotice(true);
                }
            }
        } elseif (!$couponCode && $quote->getMaxDiscountAmount() > 0) {
            $quote->setTotalsCollectedFlag(false)->setMaxDiscountAmount((float) 0.0000)->collectTotals()->save();
            $checkoutSession->setDoNotCheckForMaxDiscountAmount(false);
            $checkoutSession->setDoNotShowMaxDiscountAmountNotice(false);
        } elseif (!$couponCode) {
            $checkoutSession->setDoNotCheckForMaxDiscountAmount(false);
            $checkoutSession->setDoNotShowMaxDiscountAmountNotice(false);
        }
    }

    public function salesOrderPlaceBefore(Varien_Event_Observer $observer)
    {
        if (!Mage::app()->getStore()->isAdmin()) {
            $checkoutSession = Mage::getSingleton('checkout/session');
        } else {
            $checkoutSession = Mage::getSingleton('adminhtml/session_quote');
        }
        $quote = $checkoutSession->getQuote();
        $order = $observer->getEvent()->getOrder();
        if (($quote->getMaxDiscountAmount() > 0) && ($order->getDiscountAmount() < (-$quote->getMaxDiscountAmount()))) {
            $deltaToAddToTotals = (float)(-($order->getDiscountAmount() + $quote->getMaxDiscountAmount()));
            $order->setDiscountAmount((float)(-$quote->getMaxDiscountAmount()));
            $order->setBaseDiscountAmount((float)($order->getDiscountAmount() * $order->getStoreToOrderRate()));

            $order->setGrandTotal((float)($order->getGrandTotal() + $deltaToAddToTotals));
            $order->setBaseGrandTotal((float)($order->getBaseGrandTotal() + $deltaToAddToTotals * $order->getStoreToOrderRate()));

            $order->setMaxDiscountAmount($quote->getMaxDiscountAmount());
            $order->save();
        } elseif ($quote->getMaxDiscountAmount() > 0) {
            $order->setMaxDiscountAmount($quote->getMaxDiscountAmount());
            $order->save();
        }
    }
}