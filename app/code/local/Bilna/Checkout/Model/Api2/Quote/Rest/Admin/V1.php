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
        $trxFrom = isset($data['trx_from']) ? $data['trx_from'] : 1;
        
    	try {
            /*@var $quote Mage_Sales_Model_Quote*/
            $quote = Mage::getModel('sales/quote');
            $quote->setStoreId($storeId)
                    ->setTrxFrom($trxFrom)
                    ->setIsActive(false)
                    ->setIsMultiShipping(false)
                    ->save();
        } catch (Mage_Core_Exception $e) {
            $this->_error($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        }
        return $this->_getLocation($quote);
    }

    /**
     * Get Shopping Cart
     *
     * @param  $quoteId
     * @param  $shippingMethod
     * @param  $store
     * @return bool
     */
    protected function _retrieve()
    {
        $quoteId = $this->getRequest()->getParam('id');
        $quote = $this->__getCollection($quoteId);

        $quoteDataRaw = $quote->getData();
        
        if(empty($quoteDataRaw)){
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }

        $quoteData = $quoteDataRaw[0];
        $addresses = $this->_getAddresses(array($quoteData['entity_id']));
        $items     = $this->_getItems(array($quoteData['entity_id']));

        if ($addresses) {
            $quoteData['addresses'] = $addresses[$quoteData['entity_id']];
        }
        if ($items) {
            $quoteData['quote_items'] = $items[$quoteData['entity_id']];
        }
        
        return $quoteData;
    }

}