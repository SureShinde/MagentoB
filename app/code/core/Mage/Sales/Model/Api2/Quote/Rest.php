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
    	$orderId    = $this->getRequest()->getParam('id');
        $collection = $this->_getCollectionForSingleRetrieve($orderId);

        if ($this->_isPaymentMethodAllowed()) {
            $this->_addPaymentMethodInfo($collection);
        }
        if ($this->_isGiftMessageAllowed()) {
            $this->_addGiftMessageInfo($collection);
        }
        $this->_addTaxInfo($collection);

        $order = $collection->getItemById($orderId);

        if (!$order) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        $orderData = $order->getData();
        $addresses = $this->_getAddresses(array($orderId));
        $items     = $this->_getItems(array($orderId));
        $comments  = $this->_getComments(array($orderId));

        if ($addresses) {
            $orderData['addresses'] = $addresses[$orderId];
        }
        if ($items) {
            $orderData['order_items'] = $items[$orderId];
        }
        if ($comments) {
            $orderData['order_comments'] = $comments[$orderId];
        }
        return $orderData;
    }

}