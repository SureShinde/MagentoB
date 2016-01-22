<?php
class Moxy_MoxySellerCenter_Model_Api extends Mage_Api_Model_Resource_Abstract
{
	private function getProductItem($orders, $productIds) {
		$orderItemArr = array();

		$orders = $orders;
		foreach($orders as $order) {

			$orderItems = Mage::getModel('sales/order_item')->getCollection()->addFieldToFilter('order_id', $order->getId());
			foreach($orderItems as $item) {
				if (in_array($item->getProductId(), $productIds)) {
					array_push($orderItemArr, $item->getData());
				}
			}
		}

		return $orderItemArr;
	}


	public function overallSales($productIds, $startDate, $endDate)
    {

		$retArray = array();
		$orders = Mage::getModel('sales/order')->getCollection();
		if ($startDate != null and $endDate != null) {
			$orders = $orders->addFieldToFilter('updated_at', array('gteq' => $startDate, 'lteq' => $endDate));
		}
		$orderItemArr = $this->getProductItem($orders, $productIds);
		$totalSales = count($orderItemArr);
		$totalRevenue = 0;

		#pending order
		$pendingOrders = $orders->addFieldToFilter('status', 'pending');
		$pendingOrderItemArr = $this->getProductItem($pendingOrders, $productIds);
		$orderItems = array();
		foreach($orderItemArr as $orderItem) {
			//echo var_dump($orderItem);
			$orderItem = Mage::getModel('sales/order_item')->load($orderItem['item_id']);
			$totalRevenue = $totalRevenue + ($orderItem->getQtyToShip() + $orderItem->getOriginalPrice());
			array_push($orderItems, $orderItem->getData());
		}

		$retArray['total_revenue'] = $totalRevenue;
		$retArray['total_sales'] = $totalSales;
		$retArray['total_pending_order'] = count($pendingOrderItemArr);
		$retArray['sales'] = $orderItems;
		return $retArray;

    }


	public function listSellerOrder($productIds)
    {

		$orderItems = Mage::getModel('sales/order_item')->getCollection()->addFieldToFilter('product_id', array('in' => $productIds));
		$orderItemArr = array();
		foreach($orderItems as $item) {
			$oitem = $item->getData();
			if (!isset($orders[$item->getOrderId()])) {
				$order = Mage::getModel('sales/order')->load($item->getOrderId());
				$oitem['status'] = $order->getStatus();
				array_push($orderItemArr, $oitem);
			}
		}
		//return Mage::helper('core')->jsonEncode($orders);
		return json_decode(Mage::helper('core')->jsonEncode($orderItemArr));
    }


	public function listSellerOrderByStatus($productIds, $status)
    {
		$orders = Mage::getModel('sales/order')->getCollection()->addFieldToFilter('status', $status);
		return $this->getProductItem($orders, $productIds);
    }


	public function listInactiveProduct($productIds, $active=1)
    {
		$status = Mage_Catalog_Model_Product_Status::STATUS_ENABLED;
		if ($active == 0) {
			$status = Mage_Catalog_Model_Product_Status::STATUS_DISABLED;
		}
		$products = Mage::getModel('catalog/product')->getCollection()->addFieldToFilter('entity_id', array('in' => $productIds))
			->addFieldToFilter('status', array('eq' => $status));
		$productArray = array();
		foreach($products as $product) {

			array_push($productArray, $product->getData());
		}
		return array('total' => count($productArray), 'products' => $productArray);

    }


    public function getSalesReport($productIds, $startDate=null, $endDate=null)
    {
        $orderItems = Mage::getModel('sales/order_item')->getCollection()->addFieldToFilter('product_id', array('in' => $productIds));

        if ($startDate != null and $endDate != null) {
            $orderItems = $orderItems->addAttributeToFilter('created_at', array('from' => $startDate, 'to' => $endDate));
        }

        $orderItems = $orderItems->setOrder('created_at', 'desc');

        $return = [];

        $return['allsales'] = [];
        $return['statistic'] = [];
        $totalOrdered = 0;
        $totalCanceled = 0;
        $totalInvoiced = 0;
        $totalRevenue = 0;
        $chartArray = [];
        foreach($orderItems as $item) {
            $data = [];
            $data['created_at'] = $item->getCreatedAt();
            $data['product_id'] = $item->getProductId();
            $data['sku'] = $item->getSku();
            $data['name'] = $item->getName();
            $data['qty_ordered'] = (int)$item->getQtyOrdered();
            $data['qty_canceled'] = (int)$item->getQtyCanceled();
            $data['qty_invoiced'] = (int)$item->getQtyInvoiced();
            $data['price'] = (double)$item->getPrice();
            $data['order_id'] = $item->getOrderId();
            $data['store_id'] = $item->getStoreId();
            $data['revenue'] = $data['qty_ordered'] * $data['price'];
            $salesOrder = Mage::getModel('sales/order')->load($item->getOrderId());
            $data['order_number'] = $salesOrder->getIncrementId();
            $data['status'] = $salesOrder->getStatus();
            $data['customer_id'] = $salesOrder->getCustomerId();
            $data['customer_email'] = $salesOrder->getCustomerEmail();
            $data['customer_name'] = $salesOrder->getCustomerFirstname().' '.$salesOrder->getCustomerLastname();

            array_push($return['allsales'], $data);

            $totalOrdered += $data['qty_ordered'];
            $totalCanceled += $data['qty_canceled'];
            $totalInvoiced += $data['qty_invoiced'];
            $totalRevenue += $data['revenue'];

            $created_at = (string)$data['created_at'];
            $date = substr($created_at, 0, strpos($created_at, ' '));

            if (in_array($date, array_keys($chartArray))) {
                $chartArray[$date] += $data['revenue'];
            } else {
                $chartArray[$date] = $data['revenue'];
            }
        }

        $return['statistic']['total_ordered'] = $totalOrdered;
        $return['statistic']['total_canceled'] = $totalCanceled;
        $return['statistic']['total_invoiced'] = $totalInvoiced;
        $return['statistic']['total_pending'] = $totalOrdered - $totalCanceled - $totalInvoiced;
        $return['statistic']['average_sales'] = (int) ($totalRevenue / $totalOrdered);
        $return['statistic']['total_revenue'] = $totalRevenue;

        ksort($chartArray);
        $return['chart']['labels'] = array_keys($chartArray);
        $return['chart']['value'] = array_values($chartArray);

        return $return;
    }


    public function getNewOrders($productIds, $startDate=null, $endDate=null)
    {
        $orderItems = Mage::getModel('sales/order_item')->getCollection()->addFieldToFilter('product_id', array('in' => $productIds));

        if ($startDate != null and $endDate != null) {
            $orderItems = $orderItems->addAttributeToFilter('created_at', array('from' => $startDate, 'to' => $endDate));
        }

        $orderItems = $orderItems->setOrder('created_at', 'desc');

        $return = [];

        $totalOrdered = 0;
        $orderNumberArray = [];
        foreach($orderItems as $item) {
            $data = [];
            $data['created_at'] = $item->getCreatedAt();
            $data['product_id'] = $item->getProductId();
            $data['sku'] = $item->getSku();
            $data['name'] = $item->getName();
            $data['qty_ordered'] = (int)$item->getQtyOrdered();
            $data['price'] = (double)$item->getPrice();
            $data['order_id'] = $item->getOrderId();
            $data['store_id'] = $item->getStoreId();
            $data['revenue'] = $data['qty_ordered'] * $data['price'];
            $salesOrder = Mage::getModel('sales/order')->load($item->getOrderId());
            $data['order_number'] = $salesOrder->getIncrementId();
            $data['status'] = $salesOrder->getStatus();
            $data['customer_id'] = $salesOrder->getCustomerId();
            $data['customer_email'] = $salesOrder->getCustomerEmail();
            $data['customer_name'] = $salesOrder->getCustomerFirstname().' '.$salesOrder->getCustomerLastname();

            if ($data['status'] == 'pending') {
                if (!in_array($data['order_number'], $orderNumberArray)) {
                    array_push($return, $data);
                    array_push($orderNumberArray, $data['order_number']);
                }
            }

            if (count($return) >= 5) return $return;
        }

        return $return;
    }


    public function getPaidOrders($productIds, $startDate=null, $endDate=null)
    {
        $orderItems = Mage::getModel('sales/order_item')->getCollection()->addFieldToFilter('product_id', array('in' => $productIds));

        if ($startDate != null and $endDate != null) {
            $orderItems = $orderItems->addAttributeToFilter('created_at', array('from' => $startDate, 'to' => $endDate));
        }

        $orderItems = $orderItems->setOrder('created_at', 'desc');

        $return = [];

        $totalOrdered = 0;
        $orderNumberArray = [];
        foreach($orderItems as $item) {
            $data = [];
            $data['created_at'] = $item->getCreatedAt();
            $data['product_id'] = $item->getProductId();
            $data['sku'] = $item->getSku();
            $data['name'] = $item->getName();
            $data['qty_ordered'] = (int)$item->getQtyOrdered();
            $data['price'] = (double)$item->getPrice();
            $data['order_id'] = $item->getOrderId();
            $data['store_id'] = $item->getStoreId();
            $data['revenue'] = $data['qty_ordered'] * $data['price'];
            $salesOrder = Mage::getModel('sales/order')->load($item->getOrderId());
            $data['order_number'] = $salesOrder->getIncrementId();
            $data['status'] = $salesOrder->getStatus();
            $data['customer_id'] = $salesOrder->getCustomerId();
            $data['customer_email'] = $salesOrder->getCustomerEmail();
            $data['customer_name'] = $salesOrder->getCustomerFirstname().' '.$salesOrder->getCustomerLastname();

            if ($data['status'] == 'processing') {
                if (!in_array($data['order_number'], $orderNumberArray)) {
                    array_push($return, $data);
                    array_push($orderNumberArray, $data['order_number']);
                }
            }

            if (count($return) >= 5) return $return;
        }

        return $return;
    }


    public function getDashboardChart($productIds, $startDate=null, $endDate=null)
    {
        $orderItems = Mage::getModel('sales/order_item')->getCollection()->addFieldToFilter('product_id', array('in' => $productIds));

        if ($startDate != null and $endDate != null) {
            $orderItems = $orderItems->addAttributeToFilter('created_at', array('from' => $startDate, 'to' => $endDate));
        } else {
            $previous_week = strtotime("-1 month +1 day");
            $tomorrow = strtotime("+1 day");
            $startDate = date('Y-m-d', $previous_week);
            $endDate = date('Y-m-d', $tomorrow);

            $orderItems = $orderItems->addAttributeToFilter('created_at', array('from' => $startDate, 'to' => $endDate));
        }

        $return = [];
        $return['startDate'] = $startDate;
        $return['endDate'] = $endDate;

        $return['invoiced_revenue'] = [];
        $return['canceled_revenue'] = [];
        $return['ordered_revenue'] = [];
        $return['newpaid_revenue'] = [];

        $totalOrdered = 0;
        $totalCanceled = 0;
        $totalInvoiced = 0;

        $totalOrderedRevenue = 0;
        $totalCanceledRevenue = 0;
        $totalInvoicedRevenue = 0;
        $chartArray = [];
        foreach($orderItems as $item) {
            $orderedRevenue = 0;
            $canceledRevenue = 0;
            $invoicedRevenue = 0;
            $newandpaidRevenue = 0;

            $data = [];
            $data['created_at'] = $item->getCreatedAt();
            $data['qty_ordered'] = (int)$item->getQtyOrdered();
            $data['price'] = (double)$item->getPrice();
            $data['revenue'] = $data['qty_ordered'] * $data['price'];
            $salesOrder = Mage::getModel('sales/order')->load($item->getOrderId());
            $data['order_number'] = $salesOrder->getIncrementId();
            $data['status'] = $salesOrder->getStatus();

            $totalOrdered += $data['qty_ordered'];
            $totalCanceled += $data['qty_canceled'];
            $totalInvoiced += $data['qty_invoiced'];

            $created_at = (string)$data['created_at'];
            $date = substr($created_at, 0, strpos($created_at, ' '));

            switch($data['status']) {
                case 'invoiced':
                    $invoicedRevenue = $data['revenue'];
                    $totalInvoicedRevenue += $data['revenue'];
                    $newandpaidRevenue = $data['revenue'];
                    break;
                case 'canceled':
                    $canceledRevenue = $data['revenue'];
                    $totalCanceledRevenue += $data['revenue'];
                    break;
                default:
                    $orderedRevenue = $data['revenue'];
                    $totalOrderedRevenue += $data['revenue'];
                    $newandpaidRevenue = $data['revenue'];
            }

            if (in_array($date, array_keys($chartArray))) {
                $chartArray[$date]['invoiced_revenue'] += $invoicedRevenue;
                $chartArray[$date]['canceled_revenue'] += $canceledRevenue;
                $chartArray[$date]['ordered_revenue'] += $orderedRevenue;
                $chartArray[$date]['newpaid_revenue'] += $newandpaidRevenue;
            } else {
                $chartArray[$date]['invoiced_revenue'] = $invoicedRevenue;
                $chartArray[$date]['canceled_revenue'] = $canceledRevenue;
                $chartArray[$date]['ordered_revenue'] = $orderedRevenue;
                $chartArray[$date]['newpaid_revenue'] = $newandpaidRevenue;
            }
        }

        ksort($chartArray);
        $return['labels'] = array_keys($chartArray);
        foreach ($chartArray as $key => $value) {
            array_push($return['invoiced_revenue'], $value['invoiced_revenue']);
            array_push($return['canceled_revenue'], $value['canceled_revenue']);
            array_push($return['ordered_revenue'], $value['ordered_revenue']);
            array_push($return['newpaid_revenue'], $value['newpaid_revenue']);
        }

        return $return;
    }


    public function getSalesDetail($orderNumber, $productIds=null)
    {
        $order = Mage::getModel('sales/order')->load($orderNumber, 'increment_id');

        $return['id'] = $order->getId();
        $return['order_number'] = $order->getIncrementId();
        $return['created_at'] = $order->getCreatedAt();
        $return['customer_name'] = $order->getCustomerFirstname().' '.$order->getCustomerLastname();
        $return['status'] = $order->getStatus();
        $return['currency_code'] = $order->getOrderCurrencyCode();

        $orderItems = Mage::getModel('sales/order_item')->getCollection()->addAttributeToFilter('order_id', $order->getId());

        $return['items'] = [];
        foreach($orderItems as $item) {
            $data = [];
            $data['item_id'] = $item->getItemId();
            $data['product_id'] = $item->getProductId();
            $data['name'] = $item->getName();
            $data['qty'] = (int) $item->getQtyOrdered();
            $data['price'] = (double) $item->getPrice();
            $data['discount_percent'] = (double) $item->getDiscountPercent();
            $data['discount_amount'] = (double) $item->getDiscountAmount();
            $data['sub_total'] = $data['qty'] * $data['price'];
            $data['sub_total_with_discount'] = $data['sub_total'] - $data['discount_amount'];

            if ($productIds != null) {
                if (in_array($data['product_id'], $productIds)) {
                    array_push($return['items'], $data);
                }
            } else {
                array_push($return['items'], $data);
            }
        }

        return $return;
    }


    public function getDashboardStatistic($productIds, $startDate=null, $endDate=null)
    {
        $orderItems = Mage::getModel('sales/order_item')->getCollection()->addFieldToFilter('product_id', array('in' => $productIds));

        $aday = 60 * 60 * 24;
        $previous_month = date('Y-m-d', strtotime("-1 month +1 day"));
        $previous_week = date('Y-m-d', strtotime("-1 week +1 day"));
        $tomorrow = date('Y-m-d', strtotime("+1 day"));
        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime("-1 day"));
        $this_week_dates = [];
        for ($i=6; $i>=0; $i--) {
            array_push($this_week_dates, (date('Y-m-d', time() - $i * $aday)));
        }

        if ($startDate != null and $endDate != null) {
            $orderItems = $orderItems->addAttributeToFilter('created_at', array('from' => $startDate, 'to' => $endDate));
        } else {
            $startDate = $previous_month;
            $endDate = $tomorrow;

            $orderItems = $orderItems->addAttributeToFilter('created_at', array('from' => $startDate, 'to' => $endDate));
        }

        $return = [];
        // $return['startDate'] = $startDate;
        // $return['endDate'] = $endDate;

        // $return['invoiced_revenue'] = [];
        // $return['canceled_revenue'] = [];
        // $return['ordered_revenue'] = [];
        // $return['newpaid_revenue'] = [];

        $totalOrdered = 0;
        $totalCanceled = 0;
        $totalInvoiced = 0;

        $totalOrderedRevenue = 0;
        $totalCanceledRevenue = 0;
        $totalInvoicedRevenue = 0;
        $chartArray = [];
        foreach($orderItems as $item) {
            $orderedRevenue = 0;
            $canceledRevenue = 0;
            $invoicedRevenue = 0;
            $newandpaidRevenue = 0;

            $data = [];
            $data['created_at'] = $item->getCreatedAt();
            $data['qty_ordered'] = (int)$item->getQtyOrdered();
            $data['price'] = (double)$item->getPrice();
            $data['revenue'] = $data['qty_ordered'] * $data['price'];
            $salesOrder = Mage::getModel('sales/order')->load($item->getOrderId());
            $data['order_number'] = $salesOrder->getIncrementId();
            $data['status'] = $salesOrder->getStatus();

            $totalOrdered += $data['qty_ordered'];
            $totalCanceled += $data['qty_canceled'];
            $totalInvoiced += $data['qty_invoiced'];

            $created_at = (string)$data['created_at'];
            $date = substr($created_at, 0, strpos($created_at, ' '));

            switch($data['status']) {
                case 'invoiced':
                    $invoicedRevenue = $data['revenue'];
                    $totalInvoicedRevenue += $data['revenue'];
                    $newandpaidRevenue = $data['revenue'];
                    break;
                case 'canceled':
                    $canceledRevenue = $data['revenue'];
                    $totalCanceledRevenue += $data['revenue'];
                    break;
                default:
                    $orderedRevenue = $data['revenue'];
                    $totalOrderedRevenue += $data['revenue'];
                    $newandpaidRevenue = $data['revenue'];
            }

            if (in_array($date, array_keys($chartArray))) {
                $chartArray[$date]['invoiced_revenue'] += $invoicedRevenue;
                $chartArray[$date]['canceled_revenue'] += $canceledRevenue;
                $chartArray[$date]['ordered_revenue'] += $orderedRevenue;
                $chartArray[$date]['newpaid_revenue'] += $newandpaidRevenue;
            } else {
                $chartArray[$date]['invoiced_revenue'] = $invoicedRevenue;
                $chartArray[$date]['canceled_revenue'] = $canceledRevenue;
                $chartArray[$date]['ordered_revenue'] = $orderedRevenue;
                $chartArray[$date]['newpaid_revenue'] = $newandpaidRevenue;
            }
        }

        $bestDate = '';
        $bestDateSales = 0;

        ksort($chartArray);
        // $return['labels'] = array_keys($chartArray);
        foreach ($chartArray as $key => $value) {
            // array_push($return['invoiced_revenue'], $value['invoiced_revenue']);
            // array_push($return['canceled_revenue'], $value['canceled_revenue']);
            // array_push($return['ordered_revenue'], $value['ordered_revenue']);
            // array_push($return['newpaid_revenue'], $value['newpaid_revenue']);

            if ($value['newpaid_revenue'] > $bestDateSales) {
                $bestDate = $key;
                $bestDateSales = $value['newpaid_revenue'];
            }
        }

        $return['best_date'] = $bestDate;
        $return['best_date_sales'] = $bestDateSales;

        $return['total_sales'] = $totalOrderedRevenue + $totalInvoicedRevenue;
        $return['average_sales'] = (int) ($return['total_sales'] / 30);

        if (in_array($today, array_keys($chartArray))) {
            $return['today_sales'] = $chartArray[$today]['newpaid_revenue'];
        } else {
            $return['today_sales'] = 0;
        }

        if (in_array($yesterday, array_keys($chartArray))) {
            $return['yesterday_sales'] = $chartArray[$yesterday]['newpaid_revenue'];
        } else {
            $return['yesterday_sales'] = 0;
        }

        // $return['this_week_dates'] = $this_week_dates;

        $this_week_sales = 0;
        foreach ($this_week_dates as $key => $value) {
            if (in_array($value, array_keys($chartArray))) {
                $this_week_sales += $chartArray[$value]['newpaid_revenue'];
            }
        }
        $return['this_week_sales'] = $this_week_sales;

        return $return;
    }

    public function getBrands(){
        $options = array();
        $attribute = Mage::getSingleton('eav/config')
            ->getAttribute(Mage_Catalog_Model_Product::ENTITY, 'brand');
        if ($attribute->usesSource()) {
            $options = $attribute->getSource()->getAllOptions(false);
        }
        return $options;
    }

    public function getBrandBySKU($productSKU){
        $result = '';

        $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $productSKU);
        if ($product) {
            $attribute = Mage::getSingleton('eav/config')
                ->getAttribute(Mage_Catalog_Model_Product::ENTITY, 'brand');
            if ($attribute->usesSource()) {
                $result = $attribute->getSource()->getOptionText($product->brand);
            }
        }

        return $result;
    }

}
