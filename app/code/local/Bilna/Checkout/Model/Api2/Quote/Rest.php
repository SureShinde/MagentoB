<?php

/**
 * API2 class for rest (admin)
 *
 * @category   Bilna
 * @package    Bilna_Checkout
 * @author     Development Team <development@bilna.com>
 */
abstract class Bilna_Checkout_Model_Api2_Quote_Rest extends Bilna_Checkout_Model_Api2_Quote
{
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
		                $tableName,
		                new Zend_Db_Expr('*')
		            )
		            ->where('quote_id IN ('.implode(",",array_values($orderIds)).')');
		            /*->where('name <> ""')
		            ->order('name ASC');*/

		        $salesQuotesItem = $adapter->fetchAll($select);
                

                foreach($salesQuotesItem as $quoteItem)
                {
                	$items[$quoteItem['quote_id']][] = $itemsFilter->out($quoteItem);
            	}
            }
        }
        return $items;
    }
}