<?php

/**
 * API2 class for customer (admin)
 *
 * @category   Bilna
 * @package    Bilna_Checkout
 * @author     Development Team <development@bilna.com>
 */
class Bilna_Checkout_Model_Api2_Customer_Rest_Admin_V1 extends Bilna_Checkout_Model_Api2_Customer_Rest
{

	/**
     * Set customer for shopping cart
     *
     * @param array $data
     * @return int
     */
    protected function _create(array $data)
    {
    	$quoteId = $data['quote_id'];
    	$storeId = isset($data['store_id']) ? $data['store_id'] : 1;
    	$customerData = $data['customer'];

    	try {
	    	$quote = $this->_getQuote($quoteId, $storeId);

	    	if(!isset($customerData['mode'])){
	    		throw Mage::throwException('Customer Mode is Unknown');
	    	}

	    	switch($customerData['mode'])
	    	{
	    		case self::MODE_CUSTOMER:
	    			/** @var $customer Mage_Customer_Model_Customer */
		            $customer = $this->_getCustomer($customerData['customer_id']);		            
		            $customer->setMode(self::MODE_CUSTOMER);
		            break;
		        case self::MODE_REGISTER:
		        case self::MODE_GUEST:
		            /** @var $customer Mage_Customer_Model_Customer */
		            $customer = Mage::getModel('customer/customer')
		                ->setData($customerData);

		            if ($customer->getMode() == self::MODE_GUEST) {
		                $password = $customer->generatePassword();

		                $customer
		                    ->setPassword($password)
		                    ->setConfirmation($password);
		            }

		            $isCustomerValid = $customer->validate();
		            if ($isCustomerValid !== true && is_array($isCustomerValid)) {
		                throw Mage::throwException('Customer data invalid');
		            }
		            break;
	    	}

	    	$quote->setCustomer($customer)
                ->setCheckoutMethod($customer->getMode())
                ->setPasswordHash($customer->encryptPassword($customer->getPassword()))
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