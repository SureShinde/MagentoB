<?php
/**
 * Description of Bilna_Cod_Model_PaymentMethod
 *
 * @author  Bilna Development Team
 * @email   development@bilna.com
 */

class Bilna_Expressshipping_Model_Observer {

    public function expressQuoteAddItem(Varien_Event_Observer $observer)
    {
        $item = $observer->getEvent()->getQuoteItem();
        $product = $item->getProduct();

        if ( !(is_null($product->getExpressShipping()) || $product->getExpressShipping() == 0) )
            $item->setExpressShipping(1);

        return $this;
    }

    public function salesQuoteConvertToOrderItem(Varien_Event_Observer $observer)
    {
        $orderItem = $observer->getEvent()->getOrderItem();
        $quoteItem = $observer->getEvent()->getItem();

        if ( !(is_null($quoteItem->getExpressShipping()) || $quoteItem->getExpressShipping() == 0) )
            $orderItem->setExpressShipping(1);

        return $this;
    }

}