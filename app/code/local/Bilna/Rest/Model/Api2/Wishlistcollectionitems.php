<?php
/**
 * Description of Bilna_Rest_Model_Api2_Wishlistcollectionitems
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Rest_Model_Api2_Wishlistcollectionitems extends Bilna_Rest_Model_Api2 
{    
    //by calling this method, assume as customer id was valid as owner of collection
    protected function getWishlistCollectionItems($wishlist) 
    {
        $productWishlistCollection = Mage::getResourceModel('wishlist/item_collection')
            ->addWishlistFilter($wishlist)
            ->setVisibilityFilter();
        
        $result = [];
        $result[0] = ['total_record' => count($productWishlistCollection->getItems())];
        foreach ($productWishlistCollection->getItems() as $item) {
            $result[$item->getId()] = $item->getData();
            
            unset($result[$item->getId()]['product']);
            unset($result[$item->getId()]['name']);
            unset($result[$item->getId()]['price']);
            
            //if you need, we can get all product detail here
            /*$product = Mage::getModel('catalog/product')
            ->setStoreId(self::DEFAULT_STORE_ID)
            ->load($item->getProductId());
            $result[$item->getId()]['product'] = $product->getData();*/
        }
        
        return $result;
    }
    
}
