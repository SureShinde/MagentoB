<?php
/**
 * Description of Bilna_Rest_Model_Api2_Megamenu
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Rest_Model_Api2_Checkorder extends Mage_Api2_Model_Resource {
    
    const PARAM_PAYMENT_METHOD = '_payment_method';
    const PARAM_TAX_NAME       = '_tax_name';
    const PARAM_TAX_RATE       = '_tax_rate';

    /**
     * Retrieve a list or orders' items in a form of [order ID => array of items, ...]
     *
     * @param array $orderIds Orders identifiers
     * @return array
     */
    protected function _getItems(array $orderIds)
    {
        $items = array();

        if ($this->_isSubCallAllowed('order_item')) {
            /** @var $itemsFilter Mage_Api2_Model_Acl_Filter */
            $itemsFilter = $this->_getSubModel('order_item', array())->getFilter();
            // do items request if at least one attribute allowed
            if ($itemsFilter->getAllowedAttributes()) {
                /* @var $collection Mage_Sales_Model_Resource_Order_Item_Collection */
                $collection = Mage::getResourceModel('sales/order_item_collection');

                $collection->addAttributeToFilter('order_id', $orderIds);

                foreach ($collection->getItems() as $item) {
                    $items[$item->getOrderId()][] = $itemsFilter->out($item->toArray());
                }
            }
        }
        return $items;
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
     * @param Mage_Sales_Model_Resource_Order_Collection $collection
     * @return Mage_Sales_Model_Api2_Order
     */
    protected function _addPaymentMethodInfo(Mage_Sales_Model_Resource_Order_Collection $collection)
    {
        return $collection->getSelect()->joinLeft(
            array('payment_method' => $collection->getTable('sales/order_payment')),
            'main_table.entity_id = payment_method.parent_id',
            array('payment_method' => 'payment_method.method')
        );
    }

    /**
     * Add order tax information to select
     *
     * @param Mage_Sales_Model_Resource_Order_Collection $collection
     * @return Mage_Sales_Model_Api2_Order
     */
    protected function _addTaxInfo(Mage_Sales_Model_Resource_Order_Collection $collection)
    {
        $taxInfoFields = array();

        if ($this->_isTaxNameAllowed()) {
            $taxInfoFields['tax_name'] = 'order_tax.title';
        }
        if ($this->_isTaxRateAllowed()) {
            $taxInfoFields['tax_rate'] = 'order_tax.percent';
        }
        if ($taxInfoFields) {
            $collection->getSelect()->joinLeft(
                array('order_tax' => $collection->getTable('sales/order_tax')),
                'main_table.entity_id = order_tax.order_id',
                $taxInfoFields
            );
        }
        return $this;
    }

    /**
     * Check tax name information is allowed
     *
     * @return bool
     */
    public function _isTaxNameAllowed()
    {
        return in_array(self::PARAM_TAX_NAME, $this->getFilter()->getAllowedAttributes());
    }

    /**
     * Check tax rate information is allowed
     *
     * @return bool
     */
    public function _isTaxRateAllowed()
    {
        return in_array(self::PARAM_TAX_RATE, $this->getFilter()->getAllowedAttributes());
    }
    
    /**
     * Clean up unused string from shipping description output
     * 
     * @return string
     */
    public function _cleanUpShippingDescription($string = null)
    {
        $string = str_replace('Pilih - ', '', $string);
        return $string;
    }
}
