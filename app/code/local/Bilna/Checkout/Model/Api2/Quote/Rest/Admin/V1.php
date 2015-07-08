<?php

/**
 * API2 class for quotes (admin)
 *
 * @category   Bilna
 * @package    Bilna_Checkout
 * @author     Development Team <development@bilna.com>
 */
class Bilna_Checkout_Model_Api2_Quote_Rest_Admin_V1 extends Bilna_Checkout_Model_Api2_Quote_Rest
{
	/**
     * Create new quote for shopping cart
     *
     * @param int|string $store
     * @return int
     */
    protected function _create(array $data)
    {
    	$storeId = 1;//$this->_getStoreId($store);

    	try {
            /*@var $quote Mage_Sales_Model_Quote*/
            $quote = Mage::getModel('sales/quote');
            $quote->setStoreId($storeId)
                    ->setIsActive(false)
                    ->setIsMultiShipping(false)
                    ->save();
        } catch (Mage_Core_Exception $e) {
            $this->_error($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        }
        return $this->_getLocation($quote);
    }
}