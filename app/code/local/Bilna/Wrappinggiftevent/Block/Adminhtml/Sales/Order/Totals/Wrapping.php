<?php
/**
 * Description of Bilna_Wrappinggiftevent_Block_Adminhtml_Sales_Order_Totals_Wrapping
 * 
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Wrappinggiftevent_Block_Adminhtml_Sales_Order_Totals_Wrapping extends Mage_Adminhtml_Block_Template
{
    
    public function getOrder() {
        return $this->getParentBlock()->getOrder();
    }

    public function getSource() {
        return $this->getParentBlock()->getSource();
    }

    public function initTotals() {
        $model = Mage::getModel('wrappinggiftevent/custom_order');
        $price = $model->getByOrder($this->getOrder()->getId());
        
        if ($price) {
            $desc = $this->getWrappingDetail($price['wrapping_type']);
            $source = $this->getSource();
            $this->getParentBlock()->addTotal(new Varien_Object(array (
                'code'   => 'wrappinggiftevent',
                'strong' => false,
                'label'  => Mage::helper('sales')->__('%s (%s)', 'Wrapping fee', $desc),
                'value'  => $price['wrapping_price']
            )), 'shipping');
        }
 
        return $this;
    }

    public function getStrong() {
        return true;
    }

    public function getWrappingPrice() {
        $resource = Mage::getSingleton('core/resource');
        $adapter = $resource->getConnection('core_read');
        $tableName = $resource->getTableName('wrapping_gift_event');
        $select = $adapter->select()->from($tableName);
        $eventNames = $adapter->fetchAll($select);

        return $eventNames;
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
