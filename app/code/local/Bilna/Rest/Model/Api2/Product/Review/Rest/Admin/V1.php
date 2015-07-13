<?php
/**
 * Description of Bilna_Rest_Model_Api2_Product_Review_Rest_Admin_V1
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Rest_Model_Api2_Product_Review_Rest_Admin_V1 extends Bilna_Rest_Model_Api2_Product_Review_Rest {
    protected function _retrieveCollection() {
        $collection = Mage::getModel('review/review')->getCollection()
            ->addStoreFilter($this->_getstore()->getId())
            ->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED)
            ->addEntityFilter('product', $this->_getProduct()->getId())
            ->setDateOrder()
            ->getItems();
        
        if (count($collection) == 0) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        
        return $collection->getData();
    }
}
