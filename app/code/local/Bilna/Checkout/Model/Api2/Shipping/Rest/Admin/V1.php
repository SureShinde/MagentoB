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

            /* get reference to helper Bilna Express Shipping */
            $expressshippingHelper = Mage::helper('bilna_expressshipping');

            /* get from config whether express shipping is enabled or disabled */
            $expressShippingEnabled = Mage::getStoreConfig('bilna_expressshipping/status/enabled');

            /* get reference to helper Bilna COD */
            $codHelper = Mage::helper('cod');
            $codEnabled = $codHelper->isCodEnabled();

            $ratesResult = array();
            foreach ($groupedRates as $carrierCode => $rates ) {

                /*
$carrierName = $carrierCode;
                if (!is_null(Mage::getStoreConfig('carriers/'.$carrierCode.'/title'))) {
                    $carrierName = Mage::getStoreConfig('carriers/'.$carrierCode.'/title');
                }
*/

                foreach ($rates as $rate) {                    
                    $enabled = true;
                    $additionalMessage = '';

                    /* if express shipping is disabled in config, don't display the express shipping method */
                    if ( strpos(strtolower($rate->getMethodTitle()), 'express') !== false && $expressShippingEnabled == 0 )
                        continue;
                    else
                    /* if express shipping is enabled in config, check the condition to get whether the radio 
                    is enabled or disabled */
                    if ( strpos(strtolower($rate->getMethodTitle()), 'express') !== false && $expressShippingEnabled == 1 )
                    {
                        $enabled = $expressshippingHelper->enableStatusExpressShippingMethod($quote);
                        $additionalMessage = $expressshippingHelper->getExpressShippingExpectedDeliveredDate();
                    }

                    /* check for COD shipping */
                    if ( strpos(strtolower($rate->getMethodTitle()), 'bayar di tempat') !== false )
                    {
                        // if not eligible to show COD method, skip the remaining process
                        if (!$codHelper->showCodMethod('', $quote))
                            continue;
                    }

                    $rateItem['carrierName'] = $rate->getMethodTitle();
                    $rateItem['carrierPrice'] = $rate->getPrice();
                    $rateItem['carrierCode'] = $rate->getCode();
                    $rateItem['enabledInFrontend'] = $enabled;
                    $rateItem['additionalMessage'] = $additionalMessage;
                    $ratesResult[] = $rateItem;
                    unset($rateItem);
                }
            }
        } catch (Mage_Core_Exception $e) {
            $this->_error($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        }

        return array('rates'=>$ratesResult);
    }

    /**
     * Set shipping for shopping cart
     *
     * @param array $data
     * @return int
     */
    /*protected function _create(array $data)
    {


    }*/

}