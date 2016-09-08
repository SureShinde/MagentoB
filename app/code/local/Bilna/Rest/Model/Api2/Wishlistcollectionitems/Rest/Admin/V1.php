<?php
/**
 * Description of Bilna_Rest_Model_Api2_Wishlistcollectionitems_Rest_Admin_V1
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Rest_Model_Api2_Wishlistcollectionitems_Rest_Admin_V1 extends Bilna_Rest_Model_Api2_Wishlistcollectionitems_Rest 
{    
    protected function _retrieveCollection() 
    {
        $result = $this->_getCollectionForRetrieve();
        
        if (!$result) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        
        return $result;
    }

    protected function _getCollectionForRetrieve() 
    {
        try {            
            //get all wishlist collection item based on slug without username
            $slug = $this->getRequest()->getParam('slug');
            $partOfSlug = explode('-', $slug);
            $collectionId = $partOfSlug[0];
            $wishlistCollection = Mage::getModel('wishlist/wishlist')->load($collectionId);
            $result = [];
            
            if ($wishlistCollection->getData() && $this->filterWishlistCollectionOutput($wishlistCollection)) {              
                $profiler = Mage::getModel('socialcommerce/profile')->load($wishlistCollection->getCustomerId(), 'customer_id');
                if (!$profiler->getCustomerId()) {
                    $this->_critical('Current profile is not found.');
                }
                $customerId = $profiler->getCustomerId();
                $customer = $this->_loadCustomerById($customerId);
                $result = $wishlistCollection;
                $result['username'] = $profiler->getUsername();
                $result['slug'] = $wishlistCollection->getId().'-'.Mage::getModel('catalog/product_url')->formatUrlKey($wishlistCollection->getName());
                $result['avatar'] = $profiler->getAvatar();
                $result['gender'] = $customer->getGender();
                $result['wishlist_collection_items'] = $this->getWishlistCollectionItems($wishlistCollection);
                
                return $result;
            }
        } catch (Exception $e) {
            $this->_critical($e->getMessage());
        }
    }
}