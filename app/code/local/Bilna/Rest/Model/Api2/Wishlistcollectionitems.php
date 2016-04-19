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
    
    /**
     * Load customer by id
     *
     * @param int $id
     * @throws Mage_Api2_Exception
     * @return Mage_Customer_Model_Customer
     */
    protected function _loadCustomerById($id)
    {
        /* @var $customer Mage_Customer_Model_Customer */
        
        $customer = Mage::getModel('customer/customer')->load($id);
        
        if (!$customer->getId()) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        
        return $customer;
    }
}
