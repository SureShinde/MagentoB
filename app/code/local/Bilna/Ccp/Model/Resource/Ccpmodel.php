<?php
class Bilna_Ccp_Model_Resource_Ccpmodel extends Mage_Core_Model_Resource_Db_Abstract {
    protected function _construct() {
        $this->_init('ccp/ccpmodel', 'product_id');
    }

    public function toOptionArray() {
        $result = array();
        $order_status = Mage::getModel('sales/order_status')->getResourceCollection()->getData();
        foreach ($order_status as $key => $value) {
            $result[] = array('value'=>$value['status'], 'label'=>$value['label']);
        }
        return $result;
    }
}