<?php
/**
 * Description of Bilna_Customer_Model_Api2_Wishlistcollection
 *
 * @author Bilna Development Team <development@bilna.com>
 */
class Bilna_Customer_Model_Api2_Wishlistcollection extends Bilna_Rest_Model_Api2
{    
    public function createNewCollection($data)
    {
        $visibility = ($data['visibility'] === 'on' ? 1 : 0);
        $wishlistName = (isset($data['name'])) ? $data['name'] : null;
        $username = (isset($data['username'])) ? $data['username'] : null;
        $desc = (isset($data['desc'])) ? $data['desc'] : null;        
        $customerId = $data['customer_id'];

        try {
            $this->_createNewCollection($customerId, $wishlistName, $visibility, $desc, $data);
        } catch (Exception $e) {
            $this->_critical($e->getMessage());
        }
        
        return TRUE;
    }

    protected function _createNewCollection($customerId, $wishlistName, $visibility, $desc, $data)
    {
        $wishlist = Mage::getModel('wishlist/wishlist');

        $cover = Mage::helper('socialcommerce')->processCover($data);
    
        //if customer try to update wishlist collection based on collection id param
        if (isset($data['collection_id'])) {
            $wishlist->setWishlistId($data['collection_id']);
        }
        $wishlist->setCustomerId($customerId)
            ->setName($wishlistName)
            ->setVisibility($visibility)
            ->setDesc($desc)
            ->generateSharingCode()
            //->setCloudCover($cover)
            ->setCover($cover)
            ->save();
        
        $preset_image = $this->getRequest()->getPost('preset_image');
        
        if ($preset_image) {
            $wishlist->setCover($preset_image);
            $wishlist->save();
        }
        
        return $wishlist;
    }
    
    public function getWishlistCollection($customerId = null) 
    {
        $username = $this->_getUsername($customerId);        
        $profiler = Mage::getModel('socialcommerce/profile')->load($username, 'username');
        $result = [];
        
        if ($profiler->getCustomerId()) {

            $profilerCustomerId = $profiler->getCustomerId();
            $customer = Mage::getModel('customer/customer')->load($profilerCustomerId);
            
            # Get wishlist collection
            $wishlistCollection = Mage::getModel('wishlist/wishlist')->getCollection();
            $wishlistCollection->addFieldToFilter('customer_id', $customer->getId()); 
            $hasCollection = $wishlistCollection->count() < 1 ? false : true;
            
            if ($wishlistCollection->getData() && $hasCollection) {
                $result[0]['total_record'] = $wishlistCollection->getSize();
                foreach($wishlistCollection as $key => $value) {
                    $result[$key] = $value->getData();
                }

                return $result;
            }
        }
        
        return FALSE;
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
    
    protected function _getUsername($customerId = null) 
    {
        $customer = $this->_loadCustomerById($customerId);
        $customerData = $customer->getData();
        
        $username = null;

        if (!isset($customerData['entity_id'])) {
            $this->_critical('No customer account specified.');
        }

        $customerProfile = Mage::getModel('socialcommerce/profile')->load($customerData['entity_id'], 'customer_id');
        $customerProfileData = $customerProfile->getData();

        if (!isset($customerProfileData['entity_id'])) {
            $username = Mage::helper('socialcommerce')->createTemporaryProfile($customer);
        } else {
            $username = $customerProfileData['username'];
        }
        
        return $username;
    }
    
    /** 
     * Add new item into collection.
     * 
     * bodyParams:
     * {"wishlist_id":"35824","name":"","desc":"","collection_id":"","image_url":"","visibility":"on","preset_image":"","product_id":"62978"}
     * 
     */
    public function addNewWishlistCollectionItem($data = array()) 
    {
        $customer = $this->_loadCustomerById($data['customer_id']);
        
        $wishlistId = (isset($data['wishlist_id'])) ? $data['wishlist_id'] : null;
        $itemDescription = (isset($data['item_description'])) ? $data['item_description'] : null;
        $productId = (isset($data['product_id'])) ? $data['product_id'] : null;
        $wishlistName = (isset($data['name'])) ? $data['name'] : null;
        $wishlistDescription = (isset($data['desc'])) ? $data['desc'] : null;
        unset($data['addnewitem']);
        unset($data['customer_id']);
        try {

            # She want to create a new collection first
            if ($wishlistName) {
                $visibility = ($data['visibility'] === 'on' ? 1 : 0);
                $wishlist = $this->_createNewCollection($customer->getId(), $wishlistName, $visibility, $wishlistDescription, $data);
            } else {
                $wishlist = Mage::getModel('wishlist/wishlist')->load($wishlistId);
            }
            
            $product = Mage::getModel('catalog/product')->load($productId);
            
            $item = Mage::getModel('wishlist/item');
            $item->setProductId($product->getId())
                ->setWishlistId($wishlist->getId())
                ->setAddedAt(now())
                ->setStoreId(self::DEFAULT_STORE_ID)
                ->setOptions($product->getCustomOptions())
                ->setProduct($product)
                ->setQty(1)
                ->save();
            
        } catch (Exception $e) {
            $this->_critical($e->getMessage());
        }
        
        return $wishlist->getData();
    }
    
    /**
     * Delete item collection bu request params.
     * 
     * paramters:
     * user=m-khairul-azami-s-kom&wlid=35823&proid=62978&url=/
     */
    public function deleteWishlistCollectionItem()
    {
        # Get value from query string
        if ($this->getRequest()->getParam('user')) {

            try {

                # Populate sent data, validate & sanitize

                $customerId = $this->getRequest()->getParam('customer_id');

                $username = $this->getRequest()->getParam('user');
                $proid = $this->getRequest()->getParam('proid');
                $wlid = $this->getRequest()->getParam('wlid');

                $wishlist = Mage::getModel('wishlist/wishlist')->load($wlid);
                $name = $wishlist->getName();
                $urlname = Mage::getModel('catalog/product_url')->formatUrlKey($name);
                
                $w = Mage::getSingleton('core/resource')->getConnection('core_write');
                $result = $w->query('DELETE FROM wishlist_item WHERE wishlist_id ='.$wlid.' and product_id ='.$proid);
                
                return $result;
                
            } catch (Exception $e) {
                $this->_critical($e->getMessage());
            }
        }
        
        return FALSE;
    }
}
