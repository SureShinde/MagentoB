<?php

/**
 * Abstract API2 class for quote address
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author     Bilna Development Team <development@bilna.com>
 */
abstract class Mage_Sales_Model_Api2_Quote_Address_Rest extends Mage_Sales_Model_Api2_Quote_Address
{
    /**#@+
     * Parameters in request used in model (usually specified in route mask)
     */
    const PARAM_QUOTE_ID     = 'quote_id';
    const PARAM_ADDRESS_TYPE = 'address_type';
    /**#@-*/

    /**
     * Retrieve quote address
     *
     * @return array
     */
    protected function _retrieve()
    {
        /** @var $address Mage_Sales_Model_Order_Address */
        $address = $this->_getCollectionForRetrieve()
            ->addAttributeToFilter('address_type', $this->getRequest()->getParam(self::PARAM_ADDRESS_TYPE))
            ->getFirstItem();
        if (!$address->getId()) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        return $address->getData();
    }

    /**
     * Retrieve order addresses
     *
     * @return array
     */
    protected function _retrieveCollection()
    {
        $collection = $this->_getCollectionForRetrieve();

        $this->_applyCollectionModifiers($collection);
        $data = $collection->load()->toArray();

        if (0 == count($data['items'])) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }

        return $data['items'];
    }

    /**
     * Retrieve collection instances
     *
     * @return Mage_Sales_Model_Resource_Order_Address_Collection
     */
    protected function _getCollectionForRetrieve()
    {
        /* @var $collection Mage_Sales_Model_Resource_Order_Address_Collection */
        $collection = Mage::getResourceModel('sales/quote_address_collection');
        $collection->addAttributeToFilter('parent_id', $this->getRequest()->getParam(self::PARAM_QUOTE_ID));

        return $collection;
    }
}