<?php

/**
 * Abstract API2 class for quote items rest
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author     Bilna Development Team <development@bilna.com>
 */
abstract class Mage_Sales_Model_Api2_Quote_Item_Rest extends Mage_Sales_Model_Api2_Quote_Item
{
    /**#@+
     * Parameters in request used in model (usually specified in route)
     */
    const PARAM_ORDER_ID = 'id';
    /**#@-*/

    /**
     * Get quote items list
     *
     * @return array
     */
    protected function _retrieveCollection()
    {
        $data = array();
        /* @var $item Mage_Sales_Model_Quote_Item */
        foreach ($this->_getCollectionForRetrieve() as $item) {
            $itemData = $item->getData();
            $itemData['status'] = $item->getStatus();
            $data[] = $itemData;
        }
        return $data;
    }
    /**
     * Retrieve quote items collection
     *
     * @return Mage_Sales_Model_Resource_Quote_Item_Collection
     */
    protected function _getCollectionForRetrieve()
    {
        /* @var $quote Mage_Sales_Model_Quote */
        $quote = $this->_loadQuoteById(
            $this->getRequest()->getParam(self::PARAM_ORDER_ID)
        );

        /* @var $collection Mage_Sales_Model_Resource_Quote_Item_Collection */
        $collection = Mage::getResourceModel('sales/quote_item_collection');
        $collection->setOrderFilter($quote->getId());
        $this->_applyCollectionModifiers($collection);
        return $collection;
    }

    /**
     * Load quote by id
     *
     * @param int $id
     * @throws Mage_Api2_Exception
     * @return Mage_Sales_Model_Quote
     */
    protected function _loadQuoteById($id)
    {
        /* @var $quote Mage_Sales_Model_Quote */
        $quote = Mage::getModel('sales/quote')->load($id);
        if (!$quote->getId()) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        return $quote;
    }
}
