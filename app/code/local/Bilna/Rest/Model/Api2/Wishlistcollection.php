<?php
/**
 * Description of Bilna_Rest_Model_Api2_Wishlistcollection
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Rest_Model_Api2_Wishlistcollection extends Bilna_Rest_Model_Api2 
{
    public function filterWishlistCollectionOutput($wishlist) 
    {
        if($wishlist) {
            $profiler = Mage::getModel('socialcommerce/profile')->load($wishlist->getCustomerId(), 'customer_id');
            if(!empty($profiler->getUsername()) && $this->getCollectionItemsTotal($wishlist) > 4) {
                $valid = TRUE;            
            } else {
                $valid = FALSE;
            }
        } else {
            $valid = FALSE;
        }
        
        return $valid;
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