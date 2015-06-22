<?php

/**
 * API2 class for orders
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author     Bilna Development Team <development@bilna.com>
 */
class Mage_Sales_Model_Api2_Quote extends Mage_Api2_Model_Resource
{
	/**#@+
     * Parameters' names in config with special ACL meaning
     */
    const PARAM_PAYMENT_METHOD = '_payment_method';
    /**#@-*/

	/**
     * Get quotes list
     *
     * @return array
     */
    protected function _retrieveCollection()
    {
        $collection = $this->_getCollectionForRetrieve();

        if ($this->_isPaymentMethodAllowed()) {
            $this->_addPaymentMethodInfo($collection);
        }
        
        $quotesData = array();

        foreach ($collection->getItems() as $quote) {
//error_log("\nquoteData : \n".print_r($quote, 1), 3, '/tmp/mageApi.log');           	
            $quotesData[$quote->getId()] = $quote->toArray();
        }

        if ($quotesData) {
            /*foreach ($this->_getAddresses(array_keys($quotesData)) as $quoteId => $addresses) {
                $quotesData[$quoteId]['addresses'] = $addresses;
            }*/
            foreach ($this->_getItems(array_keys($quotesData)) as $quoteId => $items) {
                $quotesData[$quoteId]['quote_items'] = $items;
            }
        }
        return $quotesData;
    }

    /**
     * Retrieve collection instance for orders list
     *
     * @return Mage_Sales_Model_Resource_Order_Collection
     */
    protected function _getCollectionForRetrieve()
    {
        /** @var $collection Mage_Sales_Model_Resource_Quote_Collection */
        $collection = Mage::getResourceModel('sales/quote_collection');

        $this->_applyCollectionModifiers($collection);

        return $collection;
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
     * Retrieve a list or orders' addresses in a form of [order ID => array of addresses, ...]
     *
     * @param array $orderIds Orders identifiers
     * @return array
     */
    protected function _getAddresses(array $orderIds)
    {
        $addresses = array();

        if ($this->_isSubCallAllowed('quote_address')) {
            /** @var $addressesFilter Mage_Api2_Model_Acl_Filter */
            $addressesFilter = $this->_getSubModel('quote_address', array())->getFilter();
            // do addresses request if at least one attribute allowed
            if ($addressesFilter->getAllowedAttributes()) {
                /* @var $collection Mage_Sales_Model_Resource_Order_Address_Collection */
                $collection = Mage::getResourceModel('sales/quote_address_collection');

                $collection->addAttributeToFilter('quote_id', $orderIds);

                foreach ($collection->getItems() as $item) {
                    $addresses[$item->getParentId()][] = $addressesFilter->out($item->toArray());
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
                
                //$salesQuote = Mage::getResourceModel('sales/quote_collection');
                //$salesQuote->addFieldToFilter('quote_id', $orderIds);
                /* @var $collection Mage_Sales_Model_Resource_Order_Item_Collection */
                //$collection = Mage::getResourceModel('sales/quote_item_collection');
                //$collection->setQuote($salesQuote);
                //$collection->addFieldToFilter('quote_id', $orderIds);
                //$parentQuote = Mage::getModel("sales/quote_item")->load($_item->getParentItemId());
                //$store = Mage::getSingleton('core/store')->load(1);
                //$quote = Mage::getModel('sales/quote')->setStore($store)->load($quoteId);

                $resource       = Mage::getSingleton('core/resource');
		        $adapter        = $resource->getConnection('core_read');
		        $tableName      = $resource->getTableName('sales_flat_quote_item');
		        $select = $adapter->select()
		            ->from(
		                $tableName,
		                new Zend_Db_Expr('*')
		            )
		            ->where('quote_id IN ('.implode(",",array_values($orderIds)).')');
		            /*->where('name <> ""')
		            ->order('name ASC');*/

		        $salesQuotesItem = $adapter->fetchAll($select);
                

                foreach($salesQuotesItem as $quoteItem)
                {
    error_log("\nitem ".print_r($quoteItem,1), 3, '/tmp/mageApi.log');
                	$items[$quoteItem->getOrderId()][] = $itemsFilter->out($quoteItem);
	                /*foreach ($collection->getItems() as $item) {
	error_log("\nitem ".print_r($item,1), 3, '/tmp/mageApi.log');                     	
	                    $items[$item->getOrderId()][] = $itemsFilter->out($item->toArray());
	                }*/

            	}
            }
        }
        return $items;
    }
}