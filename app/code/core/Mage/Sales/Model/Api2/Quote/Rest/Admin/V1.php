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
        $product = $this->_initProduct($productId);
//print_r($product->getPrice());die;        
        //$params['product'] = 78553; //$productId;
        //$params['qty'] =  1;

        //$cart   = $this->_getCart();*/
        /**
         * Check product availability
         */
        /*if (!$product) {
            $this->_critical('Product Not Available');
            return;
        }

        $cart->addProduct($product, $params);
        $cart->save();*/
        $email = "iwanseti1979@gmail.com";
        $customerId = 89646;
        $productId = 65922;
        $qty = 1;
        $product = $this->_initProduct($productId);
        $customer = Mage::getModel("customer/customer")->load($customerId);
        
        $storeId = $customer->getStoreId();
        $quote = Mage::getModel('sales/quote')->assignCustomer($customer); //sets ship/bill address

        $store = $quote->getStore()->load($storeId);
        $quote->setStore($store);

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

        //$quoteId = $quoteObj->getId();
/*$loc =  $this->_getLocation($quote);
echo $loc;*/
        return $this->_getLocation($quote);
    }
    /**
     * Create Quote Address
     *
     * @param array $data
     * @return string
     */
    protected function _createOld(array $data)
    {
//var_dump($data);die('bdshbchjdsbcjhdsbcjbdsjc');
        //$storeId = 1;//$data['storeId'];    	
    	$productId = 65922;//$data['entity_id'];

    	$product = $this->_initProduct($productId);
print_r($product->getPrice());die;        
        //$params['product'] = 78553; //$productId;
        //$params['qty'] =  1;

        //$cart   = $this->_getCart();*/
        /**
         * Check product availability
         */
        /*if (!$product) {
            $this->_critical('Product Not Available');
            return;
        }

        $cart->addProduct($product, $params);
        $cart->save();*/
        $email = "iwanseti1979@gmail.com";
        /*$customer = Mage::getModel("customer/customer");
        $customer->setWebsiteId(1);
        $customer->loadByEmail($email);
        $dataCustomer = $customer->getData();*/

        $collection = Mage::getModel('customer/customer')->getCollection();
        $collection->addAttributeToSelect(array(
            'entity_id', 'dob', 'firstname', 'lastname', 'email', 'suffix', 'middlename', 'prefix', 'group_id', 'default_billing', 'default_shipping'
        ));
        $collection->addFieldToFilter('email', $email);
        $dataCustomer = $collection->load();

        foreach ($collection as $customer)
        {
            //print_r($customer->getMiddlename());
            $customerId = $customer->getEntityId();
            $customerEmail = $customer->getEmail();
            $customerPrefix = $customer->getPrefix();
            $customerFirstname = $customer->getFirstname();
            $customerMiddlename = $customer->getMiddlename();
            $customerLastname = $customer->getLastname();
            $customerSuffix = $customer->getSuffix();
            $customerDob = $customer->getDob();
            $customerGroupId = $customer->getGroupId();

            $shipping = $customer->getDefaultShippingAddress();
            //print_r($shipping->toArray());

            $billing = $customer->getDefaultBillingAddress();
            //print_r($billing->toArray());
            /*foreach ($customer->getAddresses() as $address)
            {
                $data = $address->toArray();
                //print_r($data);
            }*/
           
        }

        //print_r($dataCustomer);
        if($collection)
        {
            $quote = Mage::getModel('sales/quote');
            $quote->setData(array(
                'store_id' => 1,
                'items_count' => 1,
                'items_qty' => 1,
                'customer_id' => $customerId,
                'customer_email' => $customerEmail,
                'customer_prefix' => $customerPrefix,
                'customer_firstname' => $customerFirstname,
                'customer_middlename' => $customerMiddlename,
                'customer_lastname' => $customerLastname,
                'customer_suffix'  => $customerSuffix,
                'customer_dob' => $customerDob,
                'customer_note_notify' => 1,
                'customer_group_id' => $customerGroupId
            ));



        }else{
            throw new Exception("Customer not found", 404);
            
        }

        try {

            if($quote->save())
            {
                /*block code api set shipping address*/
                $quoteAddressShipping = Mage::getModel('sales/quote_address');
                $quoteAddressShipping->setData(array(
                    'quote_id' => $quote->getId(),
                    'customer_id' => $shipping->getCustomerId(),
                    'customer_address_id' => $shipping->getEntityId(),
                    'address_type' => 'shipping',
                    'email' => $customerEmail,
                    'prefix' => $shipping->getPrefix(),
                    'firstname' => $shipping->getFirstname(),
                    'middlename' => $shipping->getMiddlename(),
                    'lastname' => $shipping->getLastname(),
                    'suffix' => $shipping->getSuffix(),
                    'company' =>$shipping->getCompany(),
                    'street' => $shipping->getStreet(),
                    'city' => $shipping->getCity(),
                    'region' => $shipping->getRegion(),
                    'region_id' => $shipping->getRegionId(),
                    'telephone' => $shipping->getTelephone(),
                    'fax' => $shipping->getFax(),
                    'postcode' => $shipping->getPostcode()
                ));

                /*block code api set billing address*/
                $quoteAddressBilling = Mage::getModel('sales/quote_address');
                $quoteAddressBilling->setData(array(
                    'quote_id' => $quote->getId(),
                    'customer_id' => $billing->getCustomerId(),
                    'customer_address_id' => $billing->getEntityId(),
                    'address_type' => 'billing',
                    'email' => $customerEmail,
                    'prefix' => $billing->getPrefix(),
                    'firstname' => $billing->getFirstname(),
                    'middlename' => $billing->getMiddlename(),
                    'lastname' => $billing->getLastname(),
                    'suffix' => $billing->getSuffix(),
                    'company' =>$billing->getCompany(),
                    'street' => $billing->getStreet(),
                    'city' => $billing->getCity(),
                    'region' => $billing->getRegion(),
                    'region_id' => $billing->getRegionId(),
                    'telephone' => $billing->getTelephone(),
                    'fax' => $billing->getFax(),
                    'postcode' => $billing->getPostcode()
                ));

                /*block code api set billing address*/
                $quoteItem = Mage::getModel('sales/quote_item');
                $quoteItem->setData(array(
                    'quote_id' => $quote->getId(),
                    'product_id' => $productId,
                    'store_id' => 1,
                    'sku' => $product->getSku(),
                    'name' => $product->getName(),
                    'weight' => $product->getWeight(),
                    'qty' => $params['qty'],
                    'price' => $product->getPrice(),
                    'base_price' => $product->getBasePrice(),

                ));

                $transaction = Mage::getModel('core/resource_transaction');
                $transaction->addObject($quoteAddressShipping);
                $transaction->addObject($quoteAddressBilling);
                $transaction->save();
            }

        } catch (Mage_Core_Exception $e) {
            $this->_error($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        } catch (Exception $e) {
            $this->_critical(self::RESOURCE_INTERNAL_ERROR);
        }

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