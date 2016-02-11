<?php

/**
 * API2 class for quotes (admin)
 *
 * @category   Bilna
 * @package    Bilna_Checkout
 * @author     Development Team <development@bilna.com>
 */
class Bilna_Checkout_Model_Api2_Product_Rest_Admin_V1 extends Bilna_Checkout_Model_Api2_Product_Rest
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
    	/*$productId = $data['product_id'];
    	$qty = isset($data['qty']) ? $data['qty'] : 1;*/

        $productsData = array($data['products']);

    	try {
	    	$quote = $this->_getQuote($quoteId, $storeId);                   

	    	if(empty($productsData))
	    	{
	    		Mage::throwException("Invalid Product Data");
	    	}

            $errors = array();
            foreach ($productsData as $productItem)
            {
                $productByItem = $this->_getProduct($productItem['product_id'], $storeId, "id");

                $productRequest = $this->_getProductRequest($productItem);
                try {
                    $result = $quote->addProduct($productByItem, $productRequest);
                    if (is_string($result)) {
                        Mage::throwException($result);
                    }
                } catch (Mage_Core_Exception $e) {
                    $errors[] = $e->getMessage();
                }
            }

            if (!empty($errors)){
                Mage::throwException(implode(PHP_EOL, $errors));
            }
            $quote->getShippingAddress()->setCollectShippingRates(true);
            $quote->getShippingAddress()->collectShippingRates();
            $quote->collectTotals()->save();

            /*$productModel = Mage::getModel('catalog/product');
            $product = $productModel->load($productId);*/


//	    	$product = $this->_getProduct($productId, $storeId, "id");

	    	/*$productRequest = $this->_getProductRequest(array(
	    		'product_id' => $productId,
	    		'qty'		 => $qty
	    	));

	    	$result = $quote->addProduct($productByItem, $productRequest);*/

//            $quoteItem = Mage::getModel('sales/quote_item')->setProduct($product);
//            $quoteItem->setStoreId($storeId);
//            $quoteItem->setQuote($quote);
//            $quoteItem->setQty($qty);

            /*if (is_string($result)) {
                throw Mage::throwException($result);
            }*/

//            $quote->addItem($quoteItem);
//            $quote->getShippingAddress()->setCollectShippingRates(true);
//            $quote->getShippingAddress()->collectShippingRates();
//            $quote->collectTotals(); // calls $address->collectTotals();
//            $quote->save();            

            //$quote->collectTotals()->save();

	    } catch (Mage_Core_Exception $e) {
            $this->_error($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        }

        return $this->_getLocation($quote);
    }

    /**
     * Update cart
     *
     * @param array $data
     * @throws Mage_Api2_Exception
     */
    protected function _update(array $data)
    {       
    	$quoteId = $data['entity_id'];
    	$storeId = isset($data['store_id']) ? $data['store_id'] : 1;
    	/*$productId = $data['product_id'];
    	$qty = isset($data['qty']) ? $data['qty'] : 1;*/
        $productsData = array($data['products']);
    	
    	try {
	    	$quote = $this->_getQuote($quoteId, $storeId);
	        
	    	if(empty($productsData))
	    	{
	    		throw Mage::throwException("Invalid Product Data");
	    	}

            foreach ($productsData as $productItem)
            {
                $productByItem = $this->_getProduct($productItem['product_id'], $storeId, "id");

                //$productRequest = $this->_getProductRequest($productItem);
                $quoteItem = $this->_getQuoteItemByProduct($quote, $productByItem,
                    $this->_getProductRequest($productItem)
                );
                
                //Mage::log(json_encode($quoteItem->getData()), null, 'mylog.log');
                //bug fix if quote item id is free product, will return call to undefined getId, 
                //since it was not an object. because the product is free, and will return null object.
                if(is_object($quoteItem)) {
                    $quoteItemId = $quoteItem->getId();
                } else {
                    $quoteItemId = array();
                }
                
                //if (!$quoteItem->getId()) {
                if (empty($quoteItemId)) {
                    return false;
                    //disabled error while product is free
                    //throw Mage::throwException("One item of products is not belong any of quote item");
                }

                if ($productItem['qty'] > 0 && !empty($quoteItemId)) {
                    $quoteItem->setQty($productItem['qty']);
                }

                $quote->addItem($quoteItem);
            }

            $quote->getShippingAddress()->setCollectShippingRates(true);
            $quote->getShippingAddress()->collectShippingRates();
            $quote->collectTotals(); // calls $address->collectTotals();
            $quote->save();

//            $productModel = Mage::getModel('catalog/product');
//            $productByItem = $productModel->load($productId);


	    	/** @var $quoteItem Mage_Sales_Model_Quote_Item */
//            $quoteItem = $this->_getQuoteItemByProduct($quote, $productByItem,
//                $this->_getProductRequest(
        //         	array(
			    	// 	'product_id' => $productId,
			    	// 	'qty'		 => $qty
			    	// )
        //         ));

        //     if (is_null($quoteItem->getId())) {
        //         throw Mage::throwException("One item of products is not belong any of quote item");
        //     }

        //     if ($qty > 0) {
        //         $quoteItem->setQty($qty);
        //     }
        //     $quote->addItem($quoteItem);
        //     $quote->getShippingAddress()->setCollectShippingRates(true);
        //     $quote->getShippingAddress()->collectShippingRates();
        //     $quote->collectTotals(); // calls $address->collectTotals();
        //     $quote->save(); 


	    } catch (Mage_Core_Exception $e) {
            $this->_error($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        }
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