<?php
    protected function _getOrdersTrackingCode(){
        $orderIds = $this->getOrderIds();
        if (empty($orderIds) || !is_array($orderIds)) {
            return;
        }
        $collection = Mage::getResourceModel('sales/order_collection')
            ->addFieldToFilter('entity_id', array('in' => $orderIds))
        ;

        $aOrders = array();
        foreach ($collection as $order) {
            $objOrder = new stdClass();
            $objOrder->transactionId = $order->getIncrementId();
            $objOrder->transactionAffiliation = Mage::app()->getStore()->getFrontendName();
            $objOrder->transactionTotal = $order->getBaseGrandTotal();
            $objOrder->transactionTax = $order->getBaseTaxAmount();
            $objOrder->transactionShipping = $order->getBaseShippingAmount();

            $aItems = array();
            foreach ($order->getAllVisibleItems() as $item) {
                $objItem = array();
                $objItem['sku'] = $item->getSku();
                $objItem['name'] = $item->getName();
                $objItem['category'] = null; //todo
                $objItem['price'] = $item->getBasePrice();
                $objItem['quantity'] = $item->getQtyOrdered();
                $aItems[] = (object) $objItem;
            }

            $objOrder->transactionProducts = $aItems;
            $aOrders[] = $objOrder;
        }

        return (empty($aOrders))? null : sprintf('dataLayer = %s;', json_encode($aOrders));
    }
?>