<?php
/**
 * Description of Bilna_Wholesale_Model
 *
 * @author  Bilna Development Team
 * @email   development@bilna.com
 */
class Bilna_Wholesale_Model_Observer
{
    // run when first adding distinct item to cart
    public function wholesaleCartAddItem(Varien_Event_Observer $observer)
    {
        $item = $observer->getEvent()->getQuoteItem();
        $stockItem = $item->getProduct()->getStockItem();
        if ( $stockItem->isWholesaleQty($item->getQty()) ) {
            $item->setIsWholesale(1);
            $item->getQuote()->setIsWholesale(1);
        }

        return $this;
    }

    public function wholesaleCartUpdateItems(Varien_Event_Observer $observer)
    {
        $data = $observer->getInfo();
        $isWholesaleOrder = false;
        foreach ($data as $itemId => $itemInfo) {
            $quoteItem = $observer->getCart()->getQuote()->getItemById($itemId);
            $product = Mage::getModel('catalog/product')->load($quoteItem->getProductId());
            if (!$product->getStockItem()->isWholesaleQty($itemInfo['qty'])) {
                $quoteItem->setIsWholesale(0);
            } else {
                $quoteItem->setIsWholesale(1);
                if (!$isWholesaleOrder) {
                    $isWholesaleOrder = true;
                }
            }
        }

        if ($isWholesaleOrder) {
            $observer->getCart()->getQuote()->setIsWholesale(1);
        } else {
            $observer->getCart()->getQuote()->setIsWholesale(0);
        }

        return $this;
    }

    public function wholesaleQuoteRemoveItem(Varien_Event_Observer $observer)
    {
        $item = $observer->getEvent()->getQuoteItem();
        $quoteItemCollections = Mage::getModel('sales/quote_item')->getCollection()->addFieldToFilter('is_wholesale', 1)->addFieldToFilter('quote_id', $item->getQuoteId());

        if ($item->getIsWholesale() && $item->getQuote()->getIsWholesale() && count($quoteItemCollections->getData()) <= 1) {
            $item->getQuote()->setIsWholesale(0);
        }

        return $this;
    }

    public function wholesaleConvertQuoteToOrder(Varien_Event_Observer $observer)
    {
        $quote = $observer->getEvent()->getQuote();
        $order = $observer->getEvent()->getOrder();
        $order->setIsWholesale($quote->getIsWholesale());

        return $this;
    }

    public function wholesaleConvertQuoteToOrderItem(Varien_Event_Observer $observer)
    {
        $orderItem = $observer->getEvent()->getOrderItem();
        $quoteItem = $observer->getEvent()->getItem();
        $orderItem->setIsWholesale($quoteItem->getIsWholesale());

        return $this;
    }
}

