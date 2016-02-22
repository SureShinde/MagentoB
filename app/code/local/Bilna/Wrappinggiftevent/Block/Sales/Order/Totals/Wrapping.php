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

class Bilna_Wrappinggiftevent_Block_Sales_Order_Totals_Wrapping extends Mage_Core_Block_Template {

    public function getOrder() {
        return $this->getParentBlock()->getOrder();
    }

    public function getSource() {
        return $this->getParentBlock()->getSource();
    }

    public function initTotals() {
        $model = Mage::getModel('wrappinggiftevent/custom_order');
        $price = $model->getByOrder($this->getOrder()->getId());
 
        if (isset ($price) && $price != 0) {
            $source = $this->getSource();
            $desc = $this->getWrappingDetail($price['wrapping_type']);
            $this->getParentBlock()->addTotal(new Varien_Object(array (
                'code'   => 'wrappinggiftevent',
                'strong' => false,
                'label'  => Mage::helper('sales')->__('%s (%s)', 'Wrapping fee', $desc),
                'value'  => $price['wrapping_price']
            )), 'points');
        }

        return $this;
    }

    public function getStrong() {
        return true;
    }

    public function getWrappingDetail($wrappingId) {
        $resource = Mage::getSingleton('core/resource');
        $adapter = $resource->getConnection('core_read');
        $tableName = $resource->getTableName('wrapping_gift_event');
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
