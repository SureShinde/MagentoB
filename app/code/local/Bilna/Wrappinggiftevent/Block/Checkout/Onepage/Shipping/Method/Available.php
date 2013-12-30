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
class Bilna_Wrappinggiftevent_Block_Checkout_Onepage_Shipping_Method_Available extends Mage_Checkout_Block_Onepage_Shipping_Method_Available
{
    public function getWrappingGiftEvent()
    {
        $resource       = Mage::getSingleton('core/resource');
        $adapter        = $resource->getConnection('core_read');
        $tableName      = $resource->getTableName('wrapping_gift_event');
        $select = $adapter->select()
            ->from(
                $tableName)
            ->where(
                'wrapping_startdate >= DATE(NOW())'
            );

        $eventNames = $adapter->fetchAll($select);

        return $eventNames;
    }

    public function getAddressWrappingEvent()
    {
        return $this->getAddress()->getWrappingType();
    }

    public function getWrappingPrice($price)
    {
        return $this->getQuote()->getStore()->convertPrice($price, true);
    }
}
