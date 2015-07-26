<?php

/**
 * API2 class for shipping (admin)
 *
 * @category   Bilna
 * @package    Bilna_Checkout
 * @author     Development Team <development@bilna.com>
 */
class Bilna_Checkout_Model_Api2_Shipping_Rest_Admin_V1 extends Bilna_Checkout_Model_Api2_Shipping_Rest
{
	/**
     * Set an Shipping Method for Shopping Cart
     *
     * @param  $quoteId
     * @param  $shippingMethod
     * @param  $store
     * @return bool
     */
    protected function _retrieve()
    {
    	$quoteId = $this->getRequest()->getParam('id');
    	$storeId = 1;

    	$quote = $this->_getQuote($quoteId, $storeId);

    	$quoteShippingAddress = $quote->getShippingAddress();
        if (is_null($quoteShippingAddress->getId())) {
            throw Mage::throwException('Shipping Address is not set');
        }

        try {
        	$quoteShippingAddress->collectShippingRates()->save();
            $groupedRates = $quoteShippingAddress->getGroupedAllShippingRates();

            $ratesResult = array();
            foreach ($groupedRates as $carrierCode => $rates ) {

                /*
$carrierName = $carrierCode;
                if (!is_null(Mage::getStoreConfig('carriers/'.$carrierCode.'/title'))) {
                    $carrierName = Mage::getStoreConfig('carriers/'.$carrierCode.'/title');
                }
*/

                foreach ($rates as $rate) {                    
                    $rateItem['carrierName'] = $rate->getMethodTitle();
                    $rateItem['carrierPrice'] = $rate->getPrice();
                    $rateItem['carrierCode'] = $rate->getCode();
                    $ratesResult[] = $rateItem;
                    unset($rateItem);
                }
            }
        } catch (Mage_Core_Exception $e) {
            $this->_error($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        }

        return array('rate'=>$ratesResult);
    }

    /**
     * Set shipping for shopping cart
     *
     * @param array $data
     * @return int
     */
    protected function _create(array $data)
    {


    }

}