<?php

class Bilna_Rules_Model_Rule_Condition_Orders extends Mage_Rule_Model_Condition_Abstract
{
    public function loadAttributeOptions()
    {
        $attributes = array(
            'order_num'    => Mage::helper('bilna_rules')->__('Number of Completed Orders'),
        );

        $this->setAttributeOption($attributes);
        return $this;
    }

    public function getAttributeElement()
    {
        $element = parent::getAttributeElement();
        $element->setShowAsText(true);
        return $element;
    }

    public function getInputType()
    {
        return 'numeric';
    }

    public function getValueElementType()
    {
        return 'text';
    }

    public function getValueSelectOptions()
    {
        $options = array();

        $key = 'value_select_options';
        if (!$this->hasData($key)) {
            $this->setData($key, $options);
        }
        return $this->getData($key);
    }

    /**
     * Validate Address Rule Condition
     *
     * @param Varien_Object $object
     * @return bool
     */
    public function validate(Varien_Object $object)
    {
        $quote = $object;
        if (!$quote instanceof Mage_Sales_Model_Quote) {
            $quote = $object->getQuote();
        }

        $num = 0;
        if ($quote->getCustomerId()){

            $resource  = Mage::getSingleton('core/resource');
            $db        = $resource->getConnection('core_read');

            $select = $db->select()
                ->from(array('o'=>$resource->getTableName('sales/order')), array())
                ->where('o.customer_id = ?', $quote->getCustomerId())
                ->where('o.status IN(?)', array('complete', 'shipping_cod'))
            ;

            if ('order_num' == $this->getAttribute()) {
                $select->from(null, array(new Zend_Db_Expr('COUNT(*)')));
            } else {
                Mage::throwException("Unknown condition attribute");
            }

            $num = $db->fetchOne($select);
        }

        return $this->validateAttribute($num);
    }
}
