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
        $order = $observer->getEvent()->getOrder();
        $shippingMethod = $order->getShippingMethod();

        // only increment the sales order daily count table if the shipping method is Express
        if (strpos($shippingMethod, "Express") !== false || strpos($shippingMethod, "Ekspres") !== false)
        {
            $todayDate = Mage::getModel('core/date')->date('Y-m-d');

            // check whether today's date is available inside the table sales_order_daily_count
            $resource = Mage::getSingleton('core/resource');
            $readConnection = $resource->getConnection('core_read');

            // check whether the order ID already exists in table sales_flat_order
            $orderId = $order->getIncrementId();
            $table = "sales_flat_order";
            $query = "SELECT increment_id FROM $table WHERE increment_id = '$orderId' LIMIT 1";
            $incrementId = $readConnection->fetchOne($query);

            // if increment ID does not exist yet, increment the sales count
            if (!$incrementId)
            {
                // check the count of today's express sales
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
    }

}