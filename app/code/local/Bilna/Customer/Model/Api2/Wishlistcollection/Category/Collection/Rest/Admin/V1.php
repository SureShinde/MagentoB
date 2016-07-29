<?php
/**
 * Description of Bilna_Customer_Model_Api2_Wishlistcollection_Category_Collection_Rest_Admin_V1
 *
 * @path    app/code/local/Bilna/Customer/Model/Api2/Wishlistcollection/Category/Collection/Rest/Admin/V1.php
 * @author  Bilna Development Team <development@bilna.com>
 */

class Bilna_Customer_Model_Api2_Wishlistcollection_Category_Collection_Rest_Admin_V1 extends Bilna_Customer_Model_Api2_Wishlistcollection_Category_Collection_Rest {
    protected function _retrieveCollection() {
        $path = $this->getRequest()->getParam('path');
        $categoryId = $this->_getCategoryIdByPath($path);
    }

    protected function _getCategoryIdByPath($path) {
        $name = $this->_getNameByPath($path);
        $collection = Mage::getModel('socialcommerce/collectioncategory')->getCollection();
        $collection->addFieldToSelect('category_id');
        $collection->getSelect()->where("LOWER(`name`) = '{$name}'");
        $collection->printLogQuery(true);exit;
    }
}
