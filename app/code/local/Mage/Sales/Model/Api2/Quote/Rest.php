<?php

/**
 * Abstract API2 class for quote
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author     Bilna Development Team <development@bilna.com>
 */
abstract class Mage_Sales_Model_Api2_Quote_Rest extends Mage_Sales_Model_Api2_Quote
{
	/**
     * Retrieve information about specified quote item
     *
     * @throws Mage_Api2_Exception
     * @return array
     */
    protected function _retrieve()
    {
        /*must be customer id then not quote id*/
        $id    = $this->getRequest()->getParam('id');
        $quote = $this->__getCollection($id);

        $quoteDataRaw = $quote->getData();
        
        if(empty($quoteDataRaw)){

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

    private function __getCollection($Id)
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

}