<?php
/**
 * Description of Bilna_Expressshipping_Helper_Data
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Expressshipping_Helper_Data extends Mage_Core_Helper_Abstract {

	public function enableStatusExpressShippingMethod($quote)
	{
		if (!$this->checkExpressShippingMethodFromCartItems($quote))
			return false;

		if (!$this->checkExpressShippingSalesCount())
			return false;

		return true;
	}

    /* show whether all the items have available express shipping method */
    private function checkExpressShippingMethodFromCartItems($quote)
    {
        $showExpress = true;

        $cartItems = $quote->getAllItems();
        foreach($cartItems as $item) {
            $product = $item->getProduct()->load();
            if ( is_null($product->getExpressShipping()) || $product->getExpressShipping() == 0 ) {
                $showExpress = false;
                break;
            }
        }

        return $showExpress;
    }

    /* check the number of sales order with express shipping method */
    private function checkExpressShippingSalesCount()
    {
        if ($this->isExpressShippingEnabled() === false) {
            return false;
        }

        $showExpress = true;

        $limit = (int) Mage::getStoreConfig('bilna_expressshipping/orderlimit/limit');
        $todayDate = Mage::getModel('core/date')->date('Y-m-d');

        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $table = "sales_order_daily_count";
        $query = "SELECT sales_count FROM $table WHERE sales_date = '$todayDate' LIMIT 1";
        $salesCount = $readConnection->fetchOne($query);

        if ($salesCount) {
            if ($salesCount >= $limit) {
                $showExpress = false;
            }
        }

        return $showExpress;
    }

    /* to get expected delivered date of the item when choosing express shipping */
    public function getExpressShippingExpectedDeliveredDate()
    {
    	$dateTime = Mage::getModel('core/date')->timestamp(time());
        $orderHour = date("H", $dateTime);
        $orderDate = date('d F Y', $dateTime);
        $nextDay = date('d F Y', strtotime($orderDate . ' +1 day'));
        
        $additionalMessage = '';
        $additionalMessage .= 'Terima pesanan tanggal <u style="text-decoration:underline">';

        if ( $orderHour < 11 )
             $additionalMessage .= $orderDate;
        else
            $additionalMessage .= $nextDay;

        $additionalMessage .= '</u>';

        return $additionalMessage;
    }

    /* check whether express shipping enabled or not */
    public function isExpressShippingEnabled()
    {
        $config = Mage::getStoreConfig('bilna_expressshipping/status/enabled');
        if ($config) {
            return true;
        }

        return false;
    }
}