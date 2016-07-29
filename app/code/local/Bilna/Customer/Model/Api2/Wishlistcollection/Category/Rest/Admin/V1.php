<?php
/**
 * Description of Bilna_Customer_Model_Api2_Wishlistcollection_Category_Rest_Admin_V1
 *
 * @path    app/code/local/Bilna/Customer/Model/Api2/Wishlistcollection/Category/Rest/Admin/V1.php
 * @author  Bilna Development Team <development@bilna.com>
 */

class Bilna_Customer_Model_Api2_Wishlistcollection_Category_Rest_Admin_V1 extends Bilna_Customer_Model_Api2_Wishlistcollection_Category_Rest {
    protected function _retrieveCollection() {
        try {
            $collection = Mage::getModel('socialcommerce/collectioncategory')->getCollection();
            $collection->addFieldToFilter('is_enabled', 1);
            $collection->addFieldToFilter('show_in_coll_page', 1);
            $collection->getSelect()->order('sort_no', 'ASC');
            $collection->load();

            $totalRecord = $collection->getSize();

            if ($totalRecord == 0) {
                $this->_critical(self::RESOURCE_NOT_FOUND);
            }

            $result = [];
            $result[0] = ['total_record' => $totalRecord];

            foreach ($collection as $row) {
                $categoryId = $row->getId();
                $categoryName = $row->getName();
                $urlKey = $row->getUrl();

                $result[$categoryId] = [
                    'category_id' => $categoryId,
                    'category_name' => $categoryName,
                    'url_key' => $urlKey,
                    'collections' => $this->_getCollectionList($categoryId),
                ];
            }

            return $result;
        }
        catch (Exception $ex) {
            Mage::logException($ex);
            $this->_critical(self::RESOURCE_INTERNAL_ERROR);
        }
    }

    protected function _getCollectionList($categoryId) {
        $collection = Mage::getModel('wishlist/wishlist')->getCollection();
        $collection->getSelect()->limit(4);
        $collection->load();

        $result = [];
        $totalRecord = $collection->getSize();

        if ($totalRecord > 0) {
            foreach ($collection as $key => $row) {
                $id = $row->getId();
                $name = $row->getName();
                $customerId = $row->getCustomerId();
                
                $result[$key] = $row->getData();
                $result[$key]['slug'] = $this->_getCollectionSlug($id, $name);
                $result[$key]['avatar'] = $this->_getCollectionAvatar($customerId);
                $result[$key]['gender'] = $this->_getCollectionGender($customerId);
            }
        }

        return $result;
    }

    protected function _getCollectionSlug($id, $name) {
        return $id . '-' . Mage::getModel('catalog/product_url')->formatUrlKey($name);
    }

    protected function _getCollectionAvatar($customerId) {
        $profile = Mage::getModel('socialcommerce/profile')->load($customerId, 'customer_id');

        return $profile->getAvatar();
    }

    protected function _getCollectionGender($customerId) {
        $customer = $this->_loadCustomerById($customerId);

        return $customer->getGender();
    }
}
