<?php
/**
 * API2 class for cart product deletion  (admin)
 *
 * @category   Bilna
 * @package    Bilna_Checkout
 * @author     Development Team <development@bilna.com>
 */

class Bilna_Checkout_Model_Api2_Product_Delete_Rest_Admin_V1 extends Bilna_Checkout_Model_Api2_Product_Rest {
    protected function _create(array $filteredData) {
        try {
            $itemId = $filteredData['item_id'];
            $quoteId = $filteredData['quote_id'];
            $quote = $this->_getQuote($quoteId);
            
            if ($quote->removeItem($itemId)) {
                $quote->setIsWholesale(0); // reset is_wholesale flag before run Mage_CatalogInventory_Model_Observer
                $quote->save();
                
                return $this->_getLocation($quote);
            }
            else {
                $this->_critical('Failed remove product', Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
            }
        }
        catch (Exception $e) {
            $this->_critical($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        }
    }
    
    /**
     * Add new product/catalog for shopping cart
     *
     * @param array $data
     * @return int
     */
    protected function _createOld(array $data) {
    	$quoteId = $data['entity_id'];
    	$storeId = isset ($data['store_id']) ? $data['store_id'] : 1;
        $productsData = [$data['products']];

    	try {
            $quote = $this->_getQuote($quoteId, $storeId);
            
            if (empty ($productsData)) {
                throw Mage::throwException("Invalid Product Data");
            }

            $errors = [];
            
            foreach ($productsData as $productItem) {
                $productByItem = $this->_getProduct($productItem['product_id'], $storeId, "id");
                $quoteItem = $this->_getQuoteItemByProduct($quote, $productByItem, $this->_getProductRequest($productItem));
                
                //- bug fix if quote item id is free product, will return call to undefined getId, 
                //- since it was not an object. because the product is free, and will return null object.
                if (is_object($quoteItem)) {
                    $quoteItemId = $quoteItem->getId();
                }
                else {
                    $quoteItemId = [];
                }
                
//                if (empty ($quoteItemId)) {
//                    $this->_removeFreeProduct($productItem['product_id'], $quote->getId());
//                    
//                    return false;
//                }

                $quote->removeItem($quoteItem->getId());
                $quote->collectTotals()->save();
                $this->salesQuoteRemoveAfter($quoteItem);
            }
        }
        catch (Mage_Core_Exception $e) {
            $this->_error($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        }

        return $this->_getLocation($quote);
    }
    
    private function _removeFreeProduct($productId = null, $quoteId = null) {
        $resource = Mage::getSingleton('core/resource');
                    
        /**
        * Retrieve the write connection
        */
        $writeConnection = $resource->getConnection('core_write');

        $tQuoteItem = $resource->getTableName('sales/quote_item');
        
        $query = "DELETE FROM $tQuoteItem WHERE product_id = ".$productId." AND quote_id = ".$quoteId."";
        /**
        * Execute the query
        */
        $writeConnection->query($query);
        
        return true;
    }
    
    protected function _checkoutCartSaveAfter($quote) {
        
    }
    
    private function salesQuoteRemoveAfter($quoteItem) {
        $option = $quoteItem->getOptionByCode('info_buyRequest');
        $buyRequest = new Varien_Object($option && $option->getValue() ? unserialize($option->getValue()) : null);
        
        if ($buyRequest->getAwAfptcRule()) {
            Mage::getResourceModel('awafptc/used')->markAsDeleted($buyRequest->getAwAfptcRule(), $quoteItem->getQuote()->getId());
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
