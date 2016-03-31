<?php
/**
 * Description of Bilna_Crossborder_Model
 *
 * @author  Bilna Development Team
 * @email   development@bilna.com
 */
class Bilna_Crossborder_Model_Observer {
    //public function expressQuoteAddItem(Varien_Event_Observer $observer)
    public function crossborderQuoteAddItem(Varien_Event_Observer $observer)
    {
        $item = $observer->getEvent()->getQuoteItem();
        $product = $item->getProduct();
        if ( !(is_null($product->getCrossBorder()) || $product->getCrossBorder() == 0) )
            $item->setCrossBorder(1);
        return $this;
    }
    
    public function salesQuoteConvertToOrderItem(Varien_Event_Observer $observer)
    {
        $orderItem = $observer->getEvent()->getOrderItem();
        $quoteItem = $observer->getEvent()->getItem();
        if ( !(is_null($quoteItem->getCrossBorder()) || $quoteItem->getCrossBorder() == 0) )
            $orderItem->setCrossBorder(1);
        return $this;
    }
}
