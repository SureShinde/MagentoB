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
            
            // ignore the parent ( configurable or bundle ), we only check the simple
            if ($item->getProductType() == 'configurable' || $item->getProductType() == 'bundle')
                continue;

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

    public function updateParentQuoteExpressShipping($quoteData)
    {
        $parents = array();

        for ($i = (count($quoteData['quote_items']) - 1) ; $i >= 0 ; $i--)
        {
            $parentItemId = $quoteData['quote_items'][$i]['parent_item_id'];
            $itemId = $quoteData['quote_items'][$i]['item_id'];
            $isExpress = ( is_null($quoteData['quote_items'][$i]['express_shipping']) ? 0 : $quoteData['quote_items'][$i]['express_shipping'] );

            // if parent does not exist
            if (is_null($parentItemId) || $parentItemId == '')
            {
                $finalIsExpress = $this->checkParentExpress($itemId, $parents, $isExpress);
                $quoteData['quote_items'][$i]['express_shipping'] = $finalIsExpress;
            }
            // if parent exists
            else
            {
                $parents[$parentItemId]['express_shipping'][] = $isExpress;
            }
        }
        
        return $quoteData;
    }

    private function checkParentExpress($itemId, $parents, $isExpress)
    {
        if (is_null($parents[$itemId]))
            return $isExpress;
        else
        {
            for ($i = 0 ; $i < count($parents[$itemId]['express_shipping']) ; $i++)
            {
                if ($parents[$itemId]['express_shipping'][$i] == 0)
                    return 0;
            }
        }

        return 1;
    }

    /* to get expected delivered date of the item when choosing express shipping */
    public function getExpressShippingExpectedDeliveredDate()
    {
    	$dateTime = Mage::getModel('core/date')->timestamp(time());
        $orderDate = date('d F Y', $dateTime);
        $nextDay = date('d F Y', strtotime($orderDate . ' +1 day'));
        
        $additionalMessage = '';
        $additionalMessage .= 'Terima pesanan tanggal <u style="text-decoration:underline">';

        if ($this->isBeforeCutOffTime())
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

    /**
     * Method to check if the current time is before Express Shippping Cut Off Time
     * @return bool
     */
    public function isBeforeCutOffTime()
    {
        $dateTime = Mage::getModel('core/date')->timestamp(time());
        $currentTime = date("H:i:s", $dateTime);
		$cutOffTime = str_replace(',', ':', Mage::getStoreConfig('bilna_expressshipping/orderlimit/cut_off'));
        if (strtotime($currentTime) < strtotime($cutOffTime)) {
            return true;
        }
        return false;
    }

    /**
     * Method to get the display format of Cut Off Time for front end
     * @return bool|string
     */
    public function getDisplayCutOffTime()
    {
        $cutOffTime = str_replace(',', ':', Mage::getStoreConfig('bilna_expressshipping/orderlimit/cut_off'));
        return date('H:i', strtotime($cutOffTime));
    }
}
