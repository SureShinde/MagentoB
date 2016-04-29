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

            $resource = Mage::getSingleton('core/resource');
            $readConnection = $resource->getConnection('core_read');
            
            $recLimit = 40;
            
            $limit = (int)$this->getRequest()->getParam('limit');
            $page = (int)$this->getRequest()->getParam('page');

            if( $page > 0 ) {
                $page = $page + 1;
                $offset = $recLimit * $page ;
            } else {
                $page = 0;
                $offset = 0;
            }
            
            $queryTotal = "SELECT g.wishlist_id, g.customer_id, g.visibility, g.customer_id, 
            g.cover, g.name, g.updated_at,
            COUNT(m.wishlist_id) AS totalItem
            FROM ".$resource->getTableName('wishlist/wishlist')." AS g
            LEFT JOIN ".$resource->getTableName('wishlist/item')." AS m ON g.wishlist_id = m.wishlist_id
            WHERE g.visibility = 1 AND g.name IS NOT NULL AND g.cover IS NOT NULL
            GROUP BY g.wishlist_id
            HAVING totalItem > 4
            ORDER BY g.updated_at desc";
            
            $query = "SELECT g.wishlist_id, g.customer_id, g.visibility, g.customer_id, 
            g.cover, g.name, g.updated_at,
            COUNT(m.wishlist_id) AS totalItem
            FROM ".$resource->getTableName('wishlist/wishlist')." AS g
            LEFT JOIN ".$resource->getTableName('wishlist/item')." AS m ON g.wishlist_id = m.wishlist_id
            WHERE g.visibility = 1 AND g.name IS NOT NULL AND g.cover IS NOT NULL
            GROUP BY g.wishlist_id
            HAVING totalItem > 4
            ORDER BY g.updated_at desc
            LIMIT $offset, $recLimit;";
            
            $countWishlists = $readConnection->fetchAll($queryTotal);            
            $wishlists = $readConnection->fetchAll($query);            
            
            if ($wishlists) {
                $wishlist_collection[0]['total_record'] = count($countWishlists);
                foreach($wishlists as $wishlist) {
                    $wishlist_collection[$wishlist['wishlist_id']] = $wishlist;
                    $wishlist_collection[$wishlist['wishlist_id']]['slug'] = $wishlist['wishlist_id'].'-'.Mage::getModel('catalog/product_url')->formatUrlKey($wishlist['name']);
                    $wishlist_collection[$wishlist['wishlist_id']]['wishlist_collection_items_total'] = $wishlist['totalItem'];
                    $profiler = Mage::getModel('socialcommerce/profile')->load($wishlist['customer_id'], 'customer_id');
                    $wishlist_collection[$wishlist['wishlist_id']]['username'] = $profiler->getUsername();
                }
            }
            
            //get all favourite wishlist collection
            $faveWishlists = Mage::getModel('wishlist/wishlist')
                ->getCollection()
                ->addFilter('visibility', 1)
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
    
    /** 
     * protected function _getCollectionForRetrieve() 
    {
        try {
            $wishlist_collection = [];
            $wishlist_collection_fav = [];

            //get all wishlist collection
            $wishlists = Mage::getModel('wishlist/wishlist')
                ->getCollection()
                ->addFilter('visibility', 1)
                ->addFieldToFilter('name', array('notnull' => true))
                ->addFieldToFilter('name', array('neq' => ' '))
                ->addFieldToFilter('cover', array('notnull' => true))
                ->setOrder('updated_at', 'desc');
            
            $wishlist_collection[0]['total_record'] = $wishlists->getSize();
            
            $this->_pagination($wishlists);
            
            $wishlists->getSelect()
            ->joinInner(
                array('wishlist_item'=> Mage::getSingleton('core/resource')->getTableName('wishlist/item')),
                'main_table.wishlist_id = wishlist_item.wishlist_id'
            )
            ->group('main_table.wishlist_id');

            $wishlists->getSelect()
            ->joinInner(
                array('wishlist_profile'=> Mage::getSingleton('core/resource')->getTableName('socialcommerce/profile')),
                'main_table.customer_id = wishlist_profile.customer_id'
            )
            ->group('main_table.customer_id');

            if ($wishlists) {
                foreach($wishlists as $wishlist) {
                    //if($this->filterWishlistCollectionOutput($wishlist)) {
                        $wishlist_collection[$wishlist->getId()] = $wishlist->getData();
                        $wishlist_collection[$wishlist->getId()]['slug'] = $wishlist->getId().'-'.Mage::getModel('catalog/product_url')->formatUrlKey($wishlist->getName());
                        $wishlist_collection[$wishlist->getId()]['wishlist_collection_items_total'] = $this->getCollectionItemsTotal($wishlist);
                        $profiler = Mage::getModel('socialcommerce/profile')->load($wishlist->getCustomerId(), 'customer_id');
                        $wishlist_collection[$wishlist->getId()]['username'] = $profiler->getUsername();
                    //}
                }
                //$wishlist_collection[1]['total_record_filter'] = (count($wishlist_collection) - 1);
            }
            
            //get all favourite wishlist collection
            $faveWishlists = Mage::getModel('wishlist/wishlist')
                ->getCollection()
                ->addFilter('visibility', 1)
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
    
    
     */
    protected function _pagination($object, $defaultPageSize = 24, $defaultCurPage = 1)
    {
        $limit = (int)$this->getRequest()->getParam('limit');
        $page = (int)$this->getRequest()->getParam('page');
        
        if ($limit > 0) {
            $object->setPageSize($limit);
        } else {
            $object->setPageSize($defaultPageSize);
        }
        if ($page > 0) {
            $object->setCurPage($page);
        } else {
            $object->setCurPage($defaultCurPage);
        }
    }
}