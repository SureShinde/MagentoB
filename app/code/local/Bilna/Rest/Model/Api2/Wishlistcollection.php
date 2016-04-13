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
            if(!empty($profiler->getUsername()) && !empty($wishlist->getName()) && $wishlist->getVisibility() == 1) {
                $valid = TRUE;            
            } elseif(!empty($wishlist->getId().'-'.Mage::getModel('catalog/product_url')->formatUrlKey($wishlist->getName()))) {
                $valid = TRUE;
            } else {
                $valid = FALSE;
            }
        } else {
            $valid = FALSE;
        }
        
        return $valid;
    }
}
