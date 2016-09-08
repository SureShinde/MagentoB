<?php
/**
 * Description of Bilna_Cod_Model_Observer
 *
 * @author  Bilna Development Team
 * @email   development@bilna.com
 */

class Bilna_Cod_Model_Observer {

    public function codQuoteAddItem(Varien_Event_Observer $observer)
    {
        $item = $observer->getEvent()->getQuoteItem();
        $product = $item->getProduct();

        if ( !(is_null($product->getCod()) || $product->getCod() == 0) )
            $item->setCod(1);

        return $this;
    }

    public function salesQuoteConvertToOrderItem(Varien_Event_Observer $observer)
    {
        $orderItem = $observer->getEvent()->getOrderItem();
        $quoteItem = $observer->getEvent()->getItem();

        if ( !(is_null($quoteItem->getCod()) || $quoteItem->getCod() == 0) )
            $orderItem->setCod(1);

        return $this;
    }
}