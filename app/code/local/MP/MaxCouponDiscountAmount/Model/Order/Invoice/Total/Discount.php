<?php

/**
 * @category    MP
 * @package     MP_MaxCouponDiscountAmount
 * @copyright   MagePhobia (http://www.magephobia.com)
 */
class MP_MaxCouponDiscountAmount_Model_Order_Invoice_Total_Discount extends Mage_Sales_Model_Order_Invoice_Total_Discount
{
    public function collect(Mage_Sales_Model_Order_Invoice $invoice)
    {
        $invoice->setDiscountAmount(0);
        $invoice->setBaseDiscountAmount(0);

        $totalDiscountAmount     = 0;
        $baseTotalDiscountAmount = 0;

        $couponMaxDiscountAmount = $invoice->getOrder()->getMaxDiscountAmount();

        if (($couponMaxDiscountAmount > 0) && ($couponMaxDiscountAmount == -$invoice->getOrder()->getDiscountAmount())) {

            foreach ($invoice->getOrder()->getInvoiceCollection() as $previousInvoice) {
                if ($previousInvoice->getDiscountAmount() != 0) {
                    $couponMaxDiscountAmount = 0;
                    break;
                }
            }

            $invoice->setDiscountAmount(-$couponMaxDiscountAmount);
            $invoice->setBaseDiscountAmount(-$couponMaxDiscountAmount * $invoice->getOrder()->getStoreToOrderRate());

            $invoice->setGrandTotal($invoice->getGrandTotal() - $couponMaxDiscountAmount);
            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() - $couponMaxDiscountAmount
                * $invoice->getOrder()->getStoreToOrderRate());
        } else {
            /**
             * Checking if shipping discount was added in previous invoices.
             * So basically if we have invoice with positive discount and it
             * was not canceled we don't add shipping discount to this one.
             */
            $addShippingDicount = true;
            foreach ($invoice->getOrder()->getInvoiceCollection() as $previusInvoice) {
                if ($previusInvoice->getDiscountAmount()) {
                    $addShippingDicount = false;
                }
            }

            if ($addShippingDicount) {
                $totalDiscountAmount     = $totalDiscountAmount + $invoice->getOrder()->getShippingDiscountAmount();
                $baseTotalDiscountAmount = $baseTotalDiscountAmount + $invoice->getOrder()->getBaseShippingDiscountAmount();
            }

            /** @var $item Mage_Sales_Model_Order_Invoice_Item */
            foreach ($invoice->getAllItems() as $item) {
                $orderItem = $item->getOrderItem();
                if ($orderItem->isDummy()) {
                     continue;
                }

                $orderItemDiscount      = (float) $orderItem->getDiscountAmount();
                $baseOrderItemDiscount  = (float) $orderItem->getBaseDiscountAmount();
                $orderItemQty       = $orderItem->getQtyOrdered();

                if ($orderItemDiscount && $orderItemQty) {

                    /**
                     * Resolve rounding problems
                     *
                     * We dont want to include the weee discount amount as the right amount
                     * is added when calculating the taxes.
                     *
                     * Also the subtotal is without weee
                     */

                    $discount = $orderItemDiscount - $orderItem->getDiscountInvoiced();
                    $baseDiscount = $baseOrderItemDiscount - $orderItem->getBaseDiscountInvoiced();

                    if (!$item->isLast()) {
                        $activeQty = $orderItemQty - $orderItem->getQtyInvoiced();
                        $discount = $invoice->roundPrice($discount / $activeQty * $item->getQty(), 'regular', true);
                        $baseDiscount = $invoice->roundPrice($baseDiscount / $activeQty * $item->getQty(), 'base', true);
                    }

                    $item->setDiscountAmount($discount);
                    $item->setBaseDiscountAmount($baseDiscount);

                    $totalDiscountAmount += $discount;
                    $baseTotalDiscountAmount += $baseDiscount;
                }
            }

            $invoice->setDiscountAmount(-$totalDiscountAmount);
            $invoice->setBaseDiscountAmount(-$baseTotalDiscountAmount);

            $invoice->setGrandTotal($invoice->getGrandTotal() - $totalDiscountAmount);
            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() - $baseTotalDiscountAmount);
        }
        return $this;
    }
}
