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
            
            $wishlist_collection = [];
            $wishlist_collection_fav = [];
            
            //get all wishlist collection
            $wishlists = Mage::getModel('wishlist/wishlist')->getCollection();
            $wishlists->setOrder('updated_at', 'desc');
            
            if ($wishlists) {
                $wishlist_collection[0]['total_record'] = $wishlists->getSize();
                $this->_pagination($wishlists);
            
                foreach($wishlists as $wishlist) {
                    $wishlist_collection[$wishlist->getId()] = $wishlist->getData();
                    $wishlist_collection[$wishlist->getId()]['slug'] = $wishlist->getId().'-'.Mage::getModel('catalog/product_url')->formatUrlKey($wishlist->getName());
                    $wishlist_collection[$wishlist->getId()]['wishlist_collection_items_total'] = $this->getCollectionItemsTotal($wishlist);
                    $profiler = Mage::getModel('socialcommerce/profile')->load($wishlist->getCustomerId(), 'customer_id');
                    $wishlist_collection[$wishlist->getId()]['username'] = $profiler->getUsername();
                }
            }
            
            //get all favourite wishlist collection
            $faveWishlists = Mage::getModel('wishlist/wishlist')->getCollection();
            $faveWishlists = $faveWishlists->addFilter('visibility', 1)
                ->addFieldToFilter('name', array('notnull' => true))
                ->addFieldToFilter('name', array('neq' => ' '))
                ->addFieldToFilter('cover', array('notnull' => true))
                ->setOrder('counter', 'DESC')
                ->setPageSize(4);
            
            if ($faveWishlists) {
                $wishlist_collection_fav[0]['total_record'] = $faveWishlists->getSize();
            
                foreach ($faveWishlists as $faveWishlist) {
                    $wishlist_collection_fav[$faveWishlist->getId()] = $faveWishlist->getData();
                    $wishlist_collection_fav[$faveWishlist->getId()]['slug'] = $faveWishlist->getId().'-'.Mage::getModel('catalog/product_url')->formatUrlKey($faveWishlist->getName());
                    $profiler = Mage::getModel('socialcommerce/profile')->load($faveWishlist->getCustomerId(), 'customer_id');
                    $wishlist_collection_fav[$faveWishlist->getId()]['username'] = $profiler->getUsername();
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
    
    protected function _pagination($object)
    {
        $limit = (int)$this->getRequest()->getParam('limit');
        $page = (int)$this->getRequest()->getParam('page');

        if ($limit) {
            $object->setPageSize($limit);
        } else {
            $object->setPageSize(10);
        }
        if ($page) {
            $object->setCurPage($page);
        } else {
            $object->setCurPage(1);
        }
    }
    
    public function getCollectionItemsTotal($wishlist)
    {
        $productWishlistCollection = Mage::getResourceModel('wishlist/item_apicollection_item')
            ->addWishlistFilter($wishlist)
            ->setOrder('added_at', 'desc')
            ->setVisibilityFilter();
        
        return $productWishlistCollection->getSize();
    }
}
