<?php
/**
 * Description of Bilna_Customer_Model_Api2_Wishlistcollection_Category_Collection_Rest_Admin_V1
 *
 * @path    app/code/local/Bilna/Customer/Model/Api2/Wishlistcollection/Category/Collection/Rest/Admin/V1.php
 * @author  Bilna Development Team <development@bilna.com>
 */

class Bilna_Customer_Model_Api2_Wishlistcollection_Category_Collection_Rest_Admin_V1 extends Bilna_Customer_Model_Api2_Wishlistcollection_Category_Collection_Rest {
    protected function _retrieveCollection() {
        $urlKey = $this->getRequest()->getParam('url_key');

        $result = [
            'category_id' => $categoryId,
            'category_name' => $categoryName,
            'url_key' => $urlKey,
            'collections' => $this->_getWishlistCollectionListByCategoryId($categoryId),
        ];
    }
}
