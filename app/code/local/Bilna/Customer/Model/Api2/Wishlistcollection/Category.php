<?php
/**
 * Description of Bilna_Customer_Model_Api2_Wishlistcollection_Category
 *
 * @path    app/code/local/Bilna/Customer/Model/Api2/Wishlistcollection/Category.php
 * @author  Bilna Development Team <development@bilna.com>
 */

class Bilna_Customer_Model_Api2_Wishlistcollection_Category extends Bilna_Customer_Model_Api2_Wishlistcollection {
    protected function _getWishlistCollectionListByCategoryId($categoryId) {
        $collection = Mage::getModel('socialcommerce/customercollection')->getCollection();
        $collection->addFieldToSelect(['collection_category_id']);
        $collection->addFieldToFilter('main_table.collection_category_id', $categoryId);
        $collection->getSelect()->joinLeft(
            ['wishlist' => Mage::getSingleton('core/resource')->getTableName('wishlist/wishlist')],
            '`wishlist`.`wishlist_id` = `main_table`.`wishlist_id`',
            ['wishlist.*']
        );
        $collection->getSelect()->joinLeft(
            ['profile' => Mage::getSingleton('core/resource')->getTableName('socialcommerce/profile')],
            '`profile`.`customer_id` = `wishlist`.`customer_id`',
            ['profile.username']
        );
        $collection->addFieldToFilter('wishlist.visibility', 1);
        $this->_applyCollectionModifiers($collection);
        $collection->load();

        return $this->_getCollectionListItems($collection);
    }
    
    protected function _getCollectionListItems($collection) {
        $result = [];
        $totalRecord = $collection->getSize();

        if ($totalRecord > 0) {
            $result[0]['total_record'] = $totalRecord;
            $sort = 1;
            
            foreach ($collection as $key => $row) {
                $wishlistId = $row->getWishlistId();
                $name = $row->getName();
                $customerId = $row->getCustomerId();

                $result[$sort] = $row->getData();
                $result[$sort]['slug'] = $this->_getCollectionSlug($wishlistId, $name);
                $result[$sort]['avatar'] = $this->_getCollectionAvatar($customerId);
                $result[$sort]['gender'] = $this->_getCollectionGender($customerId);
                $sort++;
            }
        }

        return $result;
    }

    protected function _getCollectionSlug($wishlistId, $name) {
        return $wishlistId . '-' . Mage::getModel('catalog/product_url')->formatUrlKey($name);
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
