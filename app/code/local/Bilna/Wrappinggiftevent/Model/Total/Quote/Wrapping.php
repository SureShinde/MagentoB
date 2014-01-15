<?php
/**
 *
 * @category    Bilna
 * @package     Bilna_Wrappinggiftevent
 * @copyright   Copyright (c) 2014 PT Bilna. (http://www.bilna.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * One page checkout 
 *
 * @category   Bilna
 * @category   Bilna
 * @package    Bilna_Wrappinggiftevent
 * @author     Bilna Development Team <development@bilna.com>
 */

class Bilna_Wrappinggiftevent_Model_Total_Quote_Wrapping extends Mage_Sales_Model_Quote_Address_Total_Abstract {

    public function __construct() {
        $this->setCode('wrappinggiftevent');
    }

    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        parent::collect($address);
        $quote = $address->getQuote();

        $items = $this->_getAddressItems($address);
        if (!count($items)) {
            return $this; //this makes only address type shipping to come through
        }
        $wrappingPrice = abs($quote->getWrappingPrice());

        $baseSubtotalWithDiscount = $address->getData('base_subtotal') + $address->getData('base_discount_amount');
       
        $subtotalWithDiscount = $address->getData('subtotal') + $address->getData('discount_amount');

        $address->setWrappingPrice($quote->getWrappingPrice());
        $address->setBaseWrappingPrice($quote->getWrappingPrice());

        $address->setGrandTotal($address->getGrandTotal() + $quote->getWrappingPrice());
        $address->setBaseGrandTotal($address->getBaseGrandTotal() + $quote->getWrappingPrice());
        
        $quote->setWrappingPrice($quote->getWrappingPrice());
        $quote->setBaseWrappingPrice($quote->getWrappingPrice());

        $address->setWrappingPrice($quote->getWrappingPrice());
        $address->setWrappingType($quote->getWrappingType());
        $address->setBaseWrappingPrice($quote->getWrappingPrice());

        return $this;
    }

    public function fetch(Mage_Sales_Model_Quote_Address $address) {
        $quote = $address->getQuote();
        $session = Mage::getSingleton('checkout/session');
        if (is_null($session->getQuote()->getId())) {
            $session = Mage::getSingleton('adminhtml/session_quote');
        }
        
        if ($address->getWrappingPrice()) {
            $desc  = $this->getWrappingDetail($address->getWrappingType());

            $wrappinggiftPrice = $address->getWrappingPrice();

            $address->addTotal(array (
                'code' => $this->getCode(),
                'title' => Mage::helper('sales')->__('%s (%s)', 'Wrapping fee', $desc),
                'value' => $address->getWrappingPrice()
            ));
        }
        
        return $this;
    }

    public function getWrappingDetail($wrappingId)
    {
        $resource       = Mage::getSingleton('core/resource');
        $adapter        = $resource->getConnection('core_read');
        $tableName      = $resource->getTableName('wrapping_gift_event');
        $select = $adapter->select()
            ->from(
                $tableName,
                new Zend_Db_Expr('wrapping_name')
            )
            ->where("id = $wrappingId")
            ->limit(1);

        $wrappingDetail = $adapter->fetchRow($select);

        return $wrappingDetail['wrapping_name'];
    }
}