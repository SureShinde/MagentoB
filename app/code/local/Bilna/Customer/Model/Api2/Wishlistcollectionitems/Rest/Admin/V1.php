<?php
/**
 * Description of Bilna_Customer_Model_Api2_Wishlistcollectionitems_Rest_Admin_V1
 *
 * @author Bilna Development Team <development@bilna.com>
 */
class Bilna_Customer_Model_Api2_Wishlistcollectionitems_Rest_Admin_V1 extends Bilna_Customer_Model_Api2_Wishlistcollectionitems_Rest
{
    protected function _retrieveCollection() 
    {
        $collectionForRetrieve = $this->_getCollectionForRetrieve();
        if (!$collectionForRetrieve) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        
        return $collectionForRetrieve;
    }
    
    protected function _getCollectionForRetrieve()
    {
        $username = $this->getRequest()->getParam('username');
        $collectionId = $this->getRequest()->getParam('collection_id');
        $profiler = Mage::getModel('socialcommerce/profile')->load($username, 'username');
        if (!$profiler->getCustomerId()) {
            $this->_critical('Current username is not found.');
        }
        $customerId = $profiler->getCustomerId();
        $customer = $this->_loadCustomerById($customerId);
        $wishlistCollection = Mage::getModel('wishlist/wishlist')->getCollection();
        $wishlistCollection->addFieldToFilter('wishlist_id', $collectionId); 
        $wishlistCollection->addFieldToFilter('customer_id', $customer->getId()); 
        $hasCollection = $wishlistCollection->count() < 1 ? false : true;
        
        $result = [];
        if ($wishlistCollection->getData() && $hasCollection) {
            $result['wishlist_collection'] = $wishlistCollection->getData();
            foreach($wishlistCollection as $wishlist) {
                $result['wishlist_collection_items'][] = $this->getWishlistCollectionItems($wishlist);
            }
        }
        
        return $result;
    }
}
