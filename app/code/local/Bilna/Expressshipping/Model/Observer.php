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

    public function expressSalesCancel(Varien_Event_Observer $observer)
    {
        $expressShippingHelper = Mage::helper('bilna_expressshipping');
        $order = $observer->getEvent()->getOrder();
        $shippingMethod = $order->getShippingMethod();
        $orderDate = $order->getCreatedAt();

        // only increment the sales order daily count table if the shipping method is Express
        if ( (strpos(strtolower($shippingMethod), 'express') !== false) || (strpos(strtolower($shippingMethod), 'ekspres') !== false) )
        {
            $start = $expressShippingHelper->getStartCutOffDateTime();
            $end = $expressShippingHelper->getEndCutOffDateTime();

            // check whether today's date is available inside the table sales_order_daily_count
            $resource = Mage::getSingleton('core/resource');
            $readConnection = $resource->getConnection('core_read');
            $table = "sales_order_daily_count";
            $query = "SELECT sales_count FROM $table WHERE sales_datetime_from = '$start' AND sales_datetime_to = '$end' LIMIT 1";

            $salesCount = $readConnection->fetchOne($query);
            $writeConnection = $resource->getConnection('core_write');
            if ($salesCount) {
                // update
                $query = "UPDATE $table SET sales_count = sales_count - 1 WHERE sales_datetime_from = '$start' AND sales_datetime_to = '$end' AND sales_count > 0";
            }
            $writeConnection->query($query);
        }
    }

    public function salesOrderIncrementCount(Varien_Event_Observer $observer)
    {
        $expressShippingHelper = Mage::helper('bilna_expressshipping');
        $order = $observer->getEvent()->getOrder();
        $shippingMethod = $order->getShippingMethod();

        // only increment the sales order daily count table if the shipping method is Express
        if ( (strpos(strtolower($shippingMethod), 'express') !== false) || (strpos(strtolower($shippingMethod), 'ekspres') !== false) )
        {
            $start = $expressShippingHelper->getStartCutOffDateTime();
            $end = $expressShippingHelper->getEndCutOffDateTime();

            // check whether today's date is available inside the table sales_order_daily_count
            $resource = Mage::getSingleton('core/resource');
            $readConnection = $resource->getConnection('core_read');
            $table = "sales_order_daily_count";

            $query = "SELECT sales_count FROM $table WHERE sales_datetime_from = '$start' AND sales_datetime_to = '$end' LIMIT 1";
            $salesCount = $readConnection->fetchOne($query);

            $writeConnection = $resource->getConnection('core_write');
            if ($salesCount) {
                // update
                $query = "UPDATE $table SET sales_count = sales_count + 1 WHERE sales_datetime_from = '$start' AND sales_datetime_to = '$end'";
            }
            else
            {
                // insert new
                $query = "INSERT INTO $table (sales_datetime_from, sales_datetime_to, sales_count) VALUES ('$start', '$end', 1)";
            }

            $writeConnection->query($query);
        }
    }

}