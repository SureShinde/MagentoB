<?php

/**
 * API2 class for cart product deletion  (admin)
 *
 * @category   Bilna
 * @package    Bilna_Checkout
 * @author     Development Team <development@bilna.com>
 */
class Bilna_Checkout_Model_Api2_Product_Delete_Rest_Admin_V1 extends Bilna_Checkout_Model_Api2_Product_Rest
{
	/**
     * Add new product/catalog for shopping cart
     *
     * @param array $data
     * @return int
     */
    protected function _create(array $data)
    {
    	$quoteId = $data['entity_id'];
    	$storeId = isset($data['store_id']) ? $data['store_id'] : 1;
    	//$productId = $data['product_id'];

        $productsData = array($data['products']);

    	try {
            $quote = $this->_getQuote($quoteId, $storeId);
            
            if(empty($productId))
            {
                throw Mage::throwException("Invalid Product Data");
            }

            $errors = array();
            foreach ($productsData as $productItem)
            {
                $productByItem = $this->_getProduct($productItem['product_id'], $storeId, "id");
                $quoteItem = $this->_getQuoteItemByProduct($quote, $productByItem,
                    $this->_getProductRequest(
                        array(
                            'product_id' => $productItem['product_id'],
                            'qty'        => $productItem['qty']
                        )
                ));

                if (is_null($quoteItem->getId())) {
                    throw Mage::throwException("One item of products is not belong any of quote item");
                }

                $quote->removeItem($quoteItem->getId());

                $quote->collectTotals()->save();
            }

//            $productByItem = $this->_getProduct($productId, $storeId, "id");

            /** @var $quoteItem Mage_Sales_Model_Quote_Item */
//            $quoteItem = $this->_getQuoteItemByProduct($quote, $productByItem,
//                $this->_getProductRequest(
//                    array(
//                        'product_id' => $productId,
//                        'qty'        => $qty
//                    )
//                ));

//            if (is_null($quoteItem->getId())) {
//                throw Mage::throwException("One item of products is not belong any of quote item");
//            }

//            $quote->removeItem($quoteItem->getId());

//            $quote->collectTotals()->save();

        } catch (Mage_Core_Exception $e) {
            $this->_error($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        }

        return $this->_getLocation($quote);
        
    }
}