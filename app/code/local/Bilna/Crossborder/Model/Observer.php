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

    /*public function salesOrderIncrementCount(Varien_Event_Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $shippingMethod = $order->getShippingMethod();
        // only increment the sales order daily count table if the shipping method is Express
        if (strpos($shippingMethod, "Express") !== false || strpos($shippingMethod, "Ekspres") !== false)
        {
            $todayDate = Mage::getModel('core/date')->date('Y-m-d');
            // check whether today's date is available inside the table sales_order_daily_count
            $resource = Mage::getSingleton('core/resource');
            $readConnection = $resource->getConnection('core_read');
            $table = "sales_order_daily_count";
            $query = "SELECT sales_date FROM $table WHERE sales_date = '$todayDate' LIMIT 1";
            $salesDate = $readConnection->fetchOne($query);
            $writeConnection = $resource->getConnection('core_write');
            if ($salesDate) {
                // update
                $query = "UPDATE $table SET sales_count = sales_count + 1 WHERE sales_date = '$todayDate'";
            }
            else
            {
                // insert new
                $query = "INSERT INTO $table (sales_date, sales_count) VALUES ('$todayDate', 1)";
            }
            $writeConnection->query($query);
        }
    }*/
}