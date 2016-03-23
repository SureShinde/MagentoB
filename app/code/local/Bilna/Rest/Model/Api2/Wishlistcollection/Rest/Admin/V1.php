<?php
/**
 * Description of Bilna_Rest_Model_Api2_Wishlistcollection_Rest_Admin_V1
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Rest_Model_Api2_Wishlistcollection_Rest_Admin_V1 extends Bilna_Rest_Model_Api2_Wishlistcollection_Rest 
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
            
            //get all wishlist collection
            $wishlists = Mage::getModel('wishlist/wishlist')->getCollection();
            $wishlists->setOrder('created_at', 'desc');
            
            $limit = (int)$this->getRequest()->getParam('limit');
            $page = (int)$this->getRequest()->getParam('page');
            
            if ($limit) {
                $wishlists->setPageSize($limit);
            } else {
                $wishlists->setPageSize(10);
            }
            if ($page) {
                $wishlists->setCurPage($page);
            } else {
                $wishlists->setCurPage(1);
            }
            
            //get all favourite wishlist collection
            $faveWishlists = $wishlists->addFilter('visibility', 1)
                ->addFieldToFilter('name', array('notnull' => true))
                ->addFieldToFilter('name', array('neq' => ' '))
                ->addFieldToFilter('cover', array('notnull' => true))
                ->setOrder('counter', 'DESC')
                ->setPageSize(4);
            
            $result = [];
            $wishlist_collection = [];
            $wishlist_collection_fav = [];
            
            if ($wishlists) {
                $wishlist_collection[0]['total_record'] = $wishlists->getSize();
                foreach($wishlists as $wishlist) {
                    $wishlist_collection[$wishlist->getId()] = $wishlist->getData();
                }
            }
            
            if ($faveWishlists) {
                $wishlist_collection_fav[0]['total_record'] = $wishlists->getSize();
                foreach ($faveWishlists as $faveWishlist) {
                    $wishlist_collection_fav[$faveWishlist->getId()] = $faveWishlist->getData();
                }
            }
                
            return [
                'wishlist_collection' => [$wishlist_collection], 
                'wishlist_collection_fav' => [$wishlist_collection_fav]
            ];
        } catch (Exception $e) {
            $this->_critical($e->getMessage());
        }
    }
}
