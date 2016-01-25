<?php

/**
 * Checkout api resource
 *
 * @category   Bilna
 * @package    Bilna_Checkout
 * @author     Bilna Development Team <core@magentocommerce.com>
 */
class Bilna_Checkout_Model_Api2_Resource extends Mage_Api2_Model_Resource
{
    const DEFAULT_STORE_ID = 1;
    
    public function __construct() {
        Mage::app()->getStore()->setStoreId(self::DEFAULT_STORE_ID);
    }
    
    protected function _getStore() {
        return Mage::app()->getStore();
    }
    
	/**#@+
     * Parameters' names in config with special ACL meaning
     */
    const PARAM_PAYMENT_METHOD = '_payment_method';
    /**#@-*/
    
	/**
     * Retrieve collection instance for single order
     *
     * @param int $orderId Order identifier
     * @return Mage_Sales_Model_Resource_Order_Collection
     */
    protected function _getCollectionForSingleRetrieve($quoteId)
    {
        /** @var $collection Mage_Sales_Model_Resource_Order_Collection */
        $collection = Mage::getResourceModel('sales/quote_collection');

        return $collection->addFieldToFilter('entity_id', $quoteId);
    }

    protected function __getCollection($Id)
    {

        $collection = $this->_getCollectionForSingleRetrieve($Id);

        if ($this->_isPaymentMethodAllowed()) {
            $this->_addPaymentMethodInfo($collection);
        }

        $quote = $collection->load();
 
        if (!$quote) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        return $quote;
    }

    /**
     * Check payment method information is allowed
     *
     * @return bool
     */
    public function _isPaymentMethodAllowed()
    {
        return in_array(self::PARAM_PAYMENT_METHOD, $this->getFilter()->getAllowedAttributes());
    }


    /**
     * Add order payment method field to select
     *
     * @param Mage_Sales_Model_Resource_Quote_Collection $collection
     * @return Mage_Sales_Model_Api2_Quote
     */
    protected function _addPaymentMethodInfo(Mage_Sales_Model_Resource_Quote_Collection $collection)
    {
        $collection->getSelect()->joinLeft(
            array('payment_method' => $collection->getTable('sales/quote_payment')),
            'main_table.entity_id = payment_method.quote_id',
            array('payment_method' => 'payment_method.method')
        );

        return $this;
    }

    /**
     * Retrieve a list or quotes' addresses in a form of [quote ID => array of addresses, ...]
     *
     * @param array $quoteIds Orders identifiers
     * @return array
     */
    protected function _getAddresses(array $quoteIds)
    {
        $addresses = array();

        if ($this->_isSubCallAllowed('quote_address')) {
            /** @var $addressesFilter Mage_Api2_Model_Acl_Filter */
            $addressesFilter = $this->_getSubModel('quote_address', array())->getFilter();
            // do addresses request if at least one attribute allowed
            if ($addressesFilter->getAllowedAttributes()) {               
                $resource       = Mage::getSingleton('core/resource');
                $adapter        = $resource->getConnection('core_read');
                $tableName      = $resource->getTableName('sales_flat_quote_address');
                $select = $adapter->select()
                    ->from(
                        $tableName,
                        new Zend_Db_Expr('*')
                    )
                    ->where('quote_id IN ('.implode(",",array_values($quoteIds)).')');
                    /*->where('name <> ""')
                    ->order('name ASC');*/

                $salesQuotesAddresses = $adapter->fetchAll($select);
                
                foreach($salesQuotesAddresses as $quoteAddress)
                {
                    $addresses[$quoteAddress['quote_id']][] = $addressesFilter->out($quoteAddress);
                }

            }
        }
        return $addresses;
    }

    /**
     * Retrieve a list or orders' items in a form of [order ID => array of items, ...]
     *
     * @param array $orderIds Orders identifiers
     * @return array
     */
    protected function _getItems(array $orderIds)
    {
        $items = array();

        if ($this->_isSubCallAllowed('quote_item')) {
       	
            /** @var $itemsFilter Mage_Api2_Model_Acl_Filter */
            $itemsFilter = $this->_getSubModel('quote_item', array())->getFilter();
            // do items request if at least one attribute allowed
            if ($itemsFilter->getAllowedAttributes()) {

                $resource       = Mage::getSingleton('core/resource');
		        $adapter        = $resource->getConnection('core_read');
		        $tableName      = $resource->getTableName('sales_flat_quote_item');
		        $select = $adapter->select()
		            ->from(
		                array('sfqi' => $tableName,
		                new Zend_Db_Expr('*'))
		            )
		            ->joinLeft(
                        array('sfqio' => $resource->getTableName('sales_flat_quote_item_option')),
                        'sfqi.item_id=sfqio.item_id',
                        array('code' => 'sfqio.code', 'value' => 'sfqio.value', 'option_id'=> 'sfqio.option_id')
                    )
                    ->where('quote_id IN ('.implode(",",array_values($orderIds)).')');
                    /*->where('name <> ""')
		            ->order('name ASC');*/

		        $salesQuotesItem = $adapter->fetchAll($select);
                
                foreach($salesQuotesItem as $quoteItem)
                {
                    $options[$quoteItem['item_id']]['options'][$quoteItem['option_id']]['code'] = $quoteItem['code'];
                    $options[$quoteItem['item_id']]['options'][$quoteItem['option_id']]['value'] = $quoteItem['value'];
                    $options[$quoteItem['item_id']]['quote'] = $quoteItem;
                }

                foreach($options as $qi)
                {
                    $qi['quote']['item_options'] = $qi['options'];
                    $items[$qi['quote']['quote_id']][] = $itemsFilter->out($qi['quote']);
                }

            }
        }
        return $items;
    }

    /**
     * @param Mage_Sales_Model_Quote $quote
     * @return bool
     */
    public function prepareCustomerForQuote(Mage_Sales_Model_Quote $quote)
    {
        $isNewCustomer = false;
        switch ($quote->getCheckoutMethod()) {
        case self::MODE_GUEST:
            $this->_prepareGuestQuote($quote);
            break;
        case self::MODE_REGISTER:
            $this->_prepareNewCustomerQuote($quote);
            $isNewCustomer = true;
            break;
        default:
            $this->_prepareCustomerQuote($quote);
            break;
        }

        return $isNewCustomer;
    }

    /**
     * Prepare quote for guest checkout order submit
     *
     * @param Mage_Sales_Model_Quote $quote
     * @return Mage_Checkout_Model_Api_Resource_Customer
     */
    protected function _prepareGuestQuote(Mage_Sales_Model_Quote $quote)
    {
        $quote->setCustomerId(null)
            ->setCustomerEmail($quote->getBillingAddress()->getEmail())
            ->setCustomerIsGuest(true)
            ->setCustomerGroupId(Mage_Customer_Model_Group::NOT_LOGGED_IN_ID);
        return $this;
    }

    /**
     * Prepare quote for customer registration and customer order submit
     *
     * @param Mage_Sales_Model_Quote $quote
     * @return Mage_Checkout_Model_Api_Resource_Customer
     */
    protected function _prepareNewCustomerQuote(Mage_Sales_Model_Quote $quote)
    {
        $billing    = $quote->getBillingAddress();
        $shipping   = $quote->isVirtual() ? null : $quote->getShippingAddress();

        //$customer = Mage::getModel('customer/customer');
        $customer = $quote->getCustomer();
        /* @var $customer Mage_Customer_Model_Customer */
        $customerBilling = $billing->exportCustomerAddress();
        $customer->addAddress($customerBilling);
        $billing->setCustomerAddress($customerBilling);
        $customerBilling->setIsDefaultBilling(true);
        if ($shipping && !$shipping->getSameAsBilling()) {
            $customerShipping = $shipping->exportCustomerAddress();
            $customer->addAddress($customerShipping);
            $shipping->setCustomerAddress($customerShipping);
            $customerShipping->setIsDefaultShipping(true);
        } else {
            $customerBilling->setIsDefaultShipping(true);
        }

        Mage::helper('core')->copyFieldset('checkout_onepage_quote', 'to_customer', $quote, $customer);
        $customer->setPassword($customer->decryptPassword($quote->getPasswordHash()));
        $customer->setPasswordHash($customer->hashPassword($customer->getPassword()));
        $quote->setCustomer($customer)
            ->setCustomerId(true);

        return $this;
    }

    /**
     * Prepare quote for customer order submit
     *
     * @param Mage_Sales_Model_Quote $quote
     * @return Mage_Checkout_Model_Api_Resource_Customer
     */
    protected function _prepareCustomerQuote(Mage_Sales_Model_Quote $quote)
    {
        $billing    = $quote->getBillingAddress();
        $shipping   = $quote->isVirtual() ? null : $quote->getShippingAddress();

        $customer = $quote->getCustomer();
        if (!$billing->getCustomerId() || $billing->getSaveInAddressBook()) {
            $customerBilling = $billing->exportCustomerAddress();
            $customer->addAddress($customerBilling);
            $billing->setCustomerAddress($customerBilling);
        }
        if ($shipping && ((!$shipping->getCustomerId() && !$shipping->getSameAsBilling())
            || (!$shipping->getSameAsBilling() && $shipping->getSaveInAddressBook()))) {
            $customerShipping = $shipping->exportCustomerAddress();
            $customer->addAddress($customerShipping);
            $shipping->setCustomerAddress($customerShipping);
        }

        if (isset($customerBilling) && !$customer->getDefaultBilling()) {
            $customerBilling->setIsDefaultBilling(true);
        }
        if ($shipping && isset($customerShipping) && !$customer->getDefaultShipping()) {
            $customerShipping->setIsDefaultShipping(true);
        } else if (isset($customerBilling) && !$customer->getDefaultShipping()) {
            $customerBilling->setIsDefaultShipping(true);
        }
        $quote->setCustomer($customer);

        return $this;
    }

    /**
     * Get customer address by identifier
     *
     * @param   int $addressId
     * @return  Mage_Customer_Model_Address
     */
    protected function _getCustomerAddress($addressId)
    {
        $address = Mage::getModel('customer/address')->load((int)$addressId);
        if (is_null($address->getId())) {
            throw Mage::throwException('Invalid address Id');
        }

        $address->explodeStreetAddress();
        if ($address->getRegionId()) {
            $address->setRegion($address->getRegionId());
        }
        return $address;
    }

}