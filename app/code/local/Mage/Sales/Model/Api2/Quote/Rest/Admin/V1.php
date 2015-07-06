<?php

/**
 * API2 class for quotes (admin)
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author     Bilna Development Team <development@bilna.com>
 */
class Mage_Sales_Model_Api2_Quote_Rest_Admin_V1 extends Mage_Sales_Model_Api2_Quote_Rest
{
	/**
     * Create Quote Address
     *
     * @param array $data
     * @return string
     */
    protected function _create(array $data)
    {
        /*$email = "iwanseti1979@gmail.com";
        $customerId = 89646;
        $productId = 78290;//76889;//78290;// 65922;
        $qty = 1;
        $quoteId = 690261;*/
        $product = $this->_initProduct($productId);
        $customer = Mage::getModel("customer/customer")->load($customerId);
        
        $storeId = $customer->getStoreId();
        
        if($quoteId == ''){
            $quote = Mage::getModel('sales/quote')->assignCustomer($customer); //sets ship/bill address
        }else{
            $quote = Mage::getModel('sales/quote')->load($quoteId);
            
        }
        $store = $quote->getStore()->load($storeId);
        $quote->setStore($store);
        $quote->setEntityId($quoteId);

        $productModel = Mage::getModel('catalog/product');
        $product = $productModel->load($productId);

        try {

            $quoteItem = Mage::getModel('sales/quote_item')->setProduct($product);

        } catch (Mage_Core_Exception $e) {
            $this->_error($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        } catch (Exception $e) {
            $this->_critical(self::RESOURCE_INTERNAL_ERROR);
        }

        $quoteItem->setQuote($quote);
        $quoteItem->setQty($qty);
        
        $quote->addItem($quoteItem);
        $quote->getShippingAddress()->setCollectShippingRates(true);
        $quote->getShippingAddress()->collectShippingRates();
        $quote->collectTotals(); // calls $address->collectTotals();
        $quote->save();

        return $this->_getLocation($quote);
    }

    /**
     * Initialize product instance from request data
     *
     * @return Mage_Catalog_Model_Product || false
     */
    protected function _initProduct($productId)
    {
        if ($productId) {
            $product = Mage::getModel('catalog/product')
                ->setStoreId(1)
                ->load($productId);
            if ($product->getId()) {
                return $product;
            }
        }
        return false;
    }

    /**
     * Retrieve shopping cart model object
     *
     * @return Mage_Checkout_Model_Cart
     */
    protected function _getCart()
    {
        return Mage::getSingleton('checkout/cart');
    }

    /**
     * Get checkout session model instance
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Get current active quote instance
     *
     * @return Mage_Sales_Model_Quote
     */
    protected function _getQuote()
    {
        return $this->_getCart()->getQuote();
    }


}