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
        $productWishlistCollection = Mage::getResourceModel('wishlist/item_apicollection_item')
            ->addWishlistFilter($wishlist)
            ->setVisibilityFilter();
        
        $result = [];     
        $result[0]['total_record'] = $productWishlistCollection->getSize();
        $this->_pagination($productWishlistCollection);
        
        foreach ($productWishlistCollection->getItems() as $item) {
            $result[$item->getId()] = $item->getData();
            
            //if you need, we can get all product detail here
            /*$product = Mage::getModel('catalog/product')
            ->setStoreId(self::DEFAULT_STORE_ID)
            ->load($item->getProductId());
            $result[$item->getId()]['product'] = $product->getData();*/
        }
        
        return $result;
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
    
}
