<?php
/**
 * Description of Bilna_Customer_Model_Api2_Wishlistcollection_Rest_Admin_V1
 *
 * @author Bilna Development Team <development@bilna.com>
 */
class Bilna_Customer_Model_Api2_Wishlistcollection_Rest_Admin_V1 extends Bilna_Customer_Model_Api2_Wishlistcollection_Rest
{
    /** 
     * 
     * Create new collection by customer with post method.
     * 
     * bodyParams:
     * {"name":"A1", "desc":"A1", "image_url":"", "visibility":"on", "preset_image":""}
     * 
     */
    protected function _create(array $data)
    {
        $username = $this->getRequest()->getParam('username');
        $profiler = Mage::getModel('socialcommerce/profile')->load($username, 'username');
        if (!$profiler->getCustomerId()) {
            $this->_critical('Current username is not found.');
        }
        $data['customer_id'] = $profiler->getCustomerId();
        /* @var $customer Mage_Customer_Model_Customer */
        $customer = $this->_loadCustomerById($data['customer_id']);
        
        if ($customer->getId()) {
            try {
                //if user add new item from product list frontend
                //just add addnewitem in bodyparam
                if(isset($data['addnewitem'])) {
                    $data['customer_id'] = $customer->getId();
                    $data['username'] = $username;
                    $this->addNewWishlistCollectionItem($data);
                    return TRUE;
                }
                
                if (!isset($data['name']) || $data['name'] == "") {
                    $this->_critical('Please provide name.');
                }
                
                $this->createNewCollection($data);
                
            } catch (Exception $exception) {
                $this->_critical($exception->getMessage());
            }
        } else {
            $this->_critical('No customer account specified.');
        }
    }

    protected function _retrieve()
    {
        $username = $this->getRequest()->getParam('username');
        $profiler = Mage::getModel('socialcommerce/profile')->load($username, 'username');
        if (!$profiler->getCustomerId()) {
            $this->_critical('Current username is not found.');
        }
        $customerId = $profiler->getCustomerId();
        $customer = $this->_loadCustomerById($customerId);
        $data = [];
        if ($customer->getId()) {
            try {
                $collectionId = (int)$this->getRequest()->getParam('collection_id');
                $wishlist = Mage::getModel('wishlist/wishlist')->load($collectionId);
                $wishlist->setOrder('created_at', 'desc');

                $limit = (int)$this->getRequest()->getParam('limit');
                $page = (int)$this->getRequest()->getParam('page');

                if ($limit) {
                    $wishlist->setPageSize($limit);
                } else {
                    $wishlist->setPageSize(10);
                }
                if ($page) {
                    $wishlist->setCurPage($page);
                } else {
                    $wishlist->setCurPage(1);
                }
                
                if ($wishlist) {
                    $data = $wishlist;
                }
            } catch (Exception $e) {
                $this->_critical($e->getMessage());
            }
        } else {
            $this->_critical('No customer account specified.');
        }
        
        return $data;
    }

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
        $profiler = Mage::getModel('socialcommerce/profile')->load($username, 'username');
        if (!$profiler->getCustomerId()) {
            $this->_critical('Current username is not found.');
        }
        $customerId = $profiler->getCustomerId();
        
        $customer = $this->_loadCustomerById($customerId);
        $wishlistCollections = $this->getWishlistCollection($customerId);
        
        $categories = Mage::getModel('socialcommerce/collectioncategory')->getCollection();
        
        $collection = $profiler->getData();
        $collection['gender'] = $customer->getGender();
        $categoryItem = [];
        if ($categories->getData()) {
            foreach ($categories as $category) {
                $categoryItem[$category->getCategoryId()] = $category->getName();
            }
        }
        
        $collection['categories'][] = $categoryItem;
        if ($wishlistCollections) {
            $collection['wishlist_collections'][] = $wishlistCollections;
        }
        
        return $collection;
    }

    /**
     * Delete collection
     */
    protected function _delete()
    {
        $username = $this->getRequest()->getParam('username');
        $profiler = Mage::getModel('socialcommerce/profile')->load($username, 'username');
        if (!$profiler->getCustomerId()) {
            $this->_critical('Current username is not found.');
        }
        $customerId = $profiler->getCustomerId();
        $customer = $this->_loadCustomerById($customerId);
        
        if ($customer->getId()) {
            $collectionId = (int)$this->getRequest()->getParam('collection_id');
            
            try {
                //delete item collection
                $productId = $this->getRequest()->getParam('product_id');
                
                if ($username != '' && $productId != '' && $collectionId != '') {
                    $this->deleteWishlistCollectionItem($collectionId, $productId);
                    return TRUE;
                }
                $wishlist = Mage::getModel('wishlist/wishlist')->load($collectionId);
                if ($wishlist) {
                    $wishlist->delete();
                }
            } catch (Mage_Core_Exception $e) {
                $this->_critical($e->getMessage());
            } catch (Exception $e) {
                $this->_critical(self::RESOURCE_INTERNAL_ERROR);
            }
        } else {
            $this->_critical('No customer account specified.');
        }
    }
}
