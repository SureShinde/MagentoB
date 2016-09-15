<?php
/**
 * Description of Bilna_Customer_Model_Api2_Wishlistcollection_Category_Rest_Admin_V1
 *
 * @path    app/code/local/Bilna/Customer/Model/Api2/Wishlistcollection/Category/Rest/Admin/V1.php
 * @author  Bilna Development Team <development@bilna.com>
 */

class Bilna_Customer_Model_Api2_Wishlistcollection_Category_Rest_Admin_V1 extends Bilna_Customer_Model_Api2_Wishlistcollection_Category_Rest {
    protected function _retrieve() {
        $urlKey = $this->getRequest()->getParam('url_key');
        $categoryCollection = $this->_getCategoryCollection($urlKey);
        $result = [];

        if ($categoryCollection) {
            $categoryId = $categoryCollection->getId();
            $categoryName = $categoryCollection->getName();
            $result = [
                'category_id' => $categoryId,
                'category_name' => $categoryName,
                'url_key' => $urlKey,
                'collections' => $this->_getWishlistCollectionListByCategoryId($categoryId),
            ];
        }

        return $result;
    }

    protected function _retrieveCollection() {
        try {
            $categoryCollection = $this->_getCategoryCollection();
            $totalRecord = $categoryCollection->getSize();
            $result = [];

            if ($totalRecord > 0) {
                foreach ($categoryCollection as $row) {
                    $categoryId = $row->getId();
                    $categoryName = $row->getName();
                    $urlKey = $row->getUrl();

                    $result[] = [
                        'category_id' => $categoryId,
                        'category_name' => $categoryName,
                        'url_key' => $urlKey,
                        'collections' => $this->_getWishlistCollectionListByCategoryId($categoryId),
                    ];
                }
            }

            return $result;
        }
        catch (Exception $ex) {
            Mage::logException($ex);
            $this->_critical(self::RESOURCE_INTERNAL_ERROR);
        }
    }
}
