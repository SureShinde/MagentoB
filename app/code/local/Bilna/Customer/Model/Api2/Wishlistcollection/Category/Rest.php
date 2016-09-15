<?php
/**
 * Description of Bilna_Customer_Model_Api2_Wishlistcollection_Category_Rest
 *
 * @path    app/code/local/Bilna/Customer/Model/Api2/Wishlistcollection/Category/Rest.php
 * @author  Bilna Development Team <development@bilna.com>
 */

abstract class Bilna_Customer_Model_Api2_Wishlistcollection_Category_Rest extends Bilna_Customer_Model_Api2_Wishlistcollection_Category {
    protected function _getCategoryCollection($urlKey = '') {
        $collection = Mage::getModel('socialcommerce/collectioncategory')->getCollection();
        $collection->addFieldToFilter('is_active', 1);
        $collection->getSelect()->order('sort_no', 'ASC');

        if ($urlKey) {
            $collection->addFieldToFilter('url', $urlKey);
            
            return $collection->getFirstItem();
        }
        else {
            $collection->addFieldToFilter('show_in_coll_page', 1);
            $collection->load();

            return $collection;
        }
    }
}
