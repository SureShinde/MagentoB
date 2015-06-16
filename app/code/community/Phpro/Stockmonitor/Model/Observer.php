<?php

class Phpro_Stockmonitor_Model_Observer {

    public function catalog_product_save_after(Varien_Event_Observer $observer) {
        $event = $observer->getEvent();
        $product = $event->getProduct();
        $stockitem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);

        $action = Mage::App()->getRequest()->getActionName();
        
        switch ($action) {
            
            case Mage_Index_Model_Event::TYPE_SAVE:
                $originalQty = $product->getData('stock_data/original_inventory_qty');
                $newQty = $product->getData('stock_data/qty');

                $user = Mage::getSingleton('admin/session')->getUser()->getUsername();
                if (($newQty - $originalQty) != 0) {
                    $stockmovement = Mage::getModel('stockmonitor/stockmovement');
                    $stockmovement->setProductId($product->getId());
                    $stockmovement->setIncrementId(Mage::helper('catalog')->__('NVT'));
                    $stockmovement->setOrderId(0);
                    $stockmovement->setQtyChange((($newQty - $originalQty) > 0 ? '+' . ($newQty - $originalQty) : ($newQty - $originalQty)));
                    $stockmovement->setActionPerformed('Product_Update');
                    $stockmovement->setUsername('(' . $user . ')');
                    $stockmovement->save();
                }
                break;
                case "quickCreate":
                $originalQty = $product->getData('stock_data/original_inventory_qty');
                $newQty = $product->getData('stock_data/qty');

                $user = Mage::getSingleton('admin/session')->getUser()->getUsername();
                if (($newQty - $originalQty) != 0) {
                    $stockmovement = Mage::getModel('stockmonitor/stockmovement');
                    $stockmovement->setProductId($product->getId());
                    $stockmovement->setIncrementId(Mage::helper('catalog')->__('NVT'));
                    $stockmovement->setOrderId(0);
                    $stockmovement->setQtyChange((($newQty - $originalQty) > 0 ? '+' . ($newQty - $originalQty) : ($newQty - $originalQty)));
                    $stockmovement->setActionPerformed('Product_Update');
                    $stockmovement->setUsername('(' . $user . ')');
                    $stockmovement->save();
                }
                break;
                    
        }
    }

    public function sales_order_place_before(Varien_Event_Observer $observer) {
        $order = $observer->getEvent()->getOrder();
        $orderItems = $order->getItemsCollection();

        foreach ($orderItems as $item) {
            $product_id = $item->product_id;
            $product = Mage::getModel('catalog/product')->load($product_id);
            $stockitem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product)->getQty();
            $stockstatus = Mage::getModel('cataloginventory/stock_status')->load($product->getId());
        }
    }

    public function sales_creditmemo_item_save_after(Varien_Event_Observer $observer) {
        $event = $observer->getEvent();
        $creditmemoitem = $event->getDataObject();
        $orderItem = $creditmemoitem->getOrderItem();
        $order = $orderItem->getOrder();
            if ($creditmemoitem->getBackToStock()) {
                $product_id = $orderItem->product_id;
                $stockmovement = Mage::getModel('stockmonitor/stockmovement');
                $stockmovement->setProductId($product_id);
                $stockmovement->setIncrementId($order->getIncrementId());
                $stockmovement->setOrderId($order->getId());
                $stockmovement->setQtyChange('+' . $creditmemoitem->getData('qty'));
                $stockmovement->setActionPerformed('Credit_Memo');
                $stockmovement->save();
            }
    }

    public function sales_order_item_save_before(Varien_Event_Observer $observer) {
        $orderItem = $observer->getEvent()->getItem();
        $order = $orderItem->getOrder();
        $product_id = $orderItem->product_id;
        $action = Mage::App()->getRequest()->getActionName();

        //getBackorders()
        switch ($action) {
            case 'saveOrder':
                break;

            case 'cancel':
                switch ($order->getStatus()) {
                    case 'canceled':
                        break;
                }
                break;

            case 'save':
                switch ($order->getStatus()) {
                    case 'processing':
                        break;
                    case 'pending':
                        break;
                    case 'closed':
                        break;
                }
                break;
        }
    }

    public function sales_order_item_save_commit_after(Varien_Event_Observer $observer) {
        $orderItem = $observer->getEvent()->getItem();
        $order = $orderItem->getOrder();
        $product_id = $orderItem->product_id;
        $action = Mage::App()->getRequest()->getActionName();

        switch ($action) {
            case 'saveOrder':
                $stockmovement = Mage::getModel('stockmonitor/stockmovement');
                $stockmovement->setProductId($product_id);
                $stockmovement->setIncrementId($order->getIncrementId());
                $stockmovement->setOrderId($order->getId());
                $stockmovement->setQtyChange('-' . $orderItem->getQtyOrdered());
                $stockmovement->setActionPerformed('Create_Order_Front');
                $stockmovement->save();
                break;

            case 'cancel':

                switch ($order->getStatus()) {
                    case 'canceled':
                        $stockmovement = Mage::getModel('stockmonitor/stockmovement');
                        $stockmovement->setProductId($product_id);
                        $stockmovement->setIncrementId($order->getIncrementId());
                        $stockmovement->setOrderId($order->getId());
                        $stockmovement->setQtyChange('+' . number_format($orderItem->getQtyOrdered(), 0));
                        $stockmovement->setActionPerformed('Cancel_Order');
                        $stockmovement->save();
                        break;
                }
                break;

            case 'save':
                switch ($order->getStatus()) {
                    case 'processing':
                        break;
                    case 'pending':
                        $stockmovement = Mage::getModel('stockmonitor/stockmovement');
                        $stockmovement->setProductId($product_id);
                        $stockmovement->setIncrementId($order->getIncrementId());
                        $stockmovement->setOrderId($order->getId());
                        $stockmovement->setQtyChange('-' . $orderItem->getQtyOrdered());
                        $stockmovement->setActionPerformed('Create_Order_Back');
                        $stockmovement->save();
                        break;
                    case 'closed':
                        break;
                }
                break;
        }
    }
}

