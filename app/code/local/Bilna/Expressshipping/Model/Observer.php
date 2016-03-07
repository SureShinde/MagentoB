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

    public function salesOrderIncrementCount(Varien_Event_Observer $observer)
    {
        $todayDate = date("Y-m-d", strtotime("+7 hours"));

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

}