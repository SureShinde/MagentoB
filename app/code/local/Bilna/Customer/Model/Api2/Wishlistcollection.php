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
        //bug fix when user not update cover, but cover change to null cover
        if(!empty($data['image_url'])) {
            $wishlist->setCover($cover);
        }

        $wishlist->setCustomerId($customerId)
            ->setName($wishlistName)
            ->setVisibility($visibility)
            ->setDesc($desc)
            ->generateSharingCode()
            //->setCloudCover($cover)
            //->setCover($cover)
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

            $resource = Mage::getSingleton('core/resource');
            $readConnection = $resource->getConnection('core_read');

            $recLimit = 40;

            $limit = (null !== $this->getRequest()->getParam('limit')) ? (int)$this->getRequest()->getParam('limit') : 40;
            $page = (null !== $this->getRequest()->getParam('page')) ? (int)$this->getRequest()->getParam('page') : 1;
            $offset = $limit * ($page - 1);
            $offset = ($offset < 0) ? 0 : $offset;
            $isOwner = (int)$this->getRequest()->getParam('is_owner');

            $customerQuery = NULL;

            if($isOwner == 1) {
                $customerQuery = "
                WHERE
                g.customer_id = $customerId
                GROUP BY g.wishlist_id
                ";
            } else {
                $customerQuery = "
                WHERE
                g.visibility = 1 AND
                g.name IS NOT NULL AND
                g.cover IS NOT NULL AND
                g.customer_id = $customerId
                GROUP BY g.wishlist_id
                HAVING totalItem > 0
                ";
            }

            $queryTotal = "SELECT g.wishlist_id, g.customer_id, g.visibility, g.customer_id,
            g.cover, g.name, g.updated_at,
            COUNT(m.wishlist_id) AS totalItem
            FROM ".$resource->getTableName('wishlist/wishlist')." AS g
            LEFT JOIN ".$resource->getTableName('wishlist/item')." AS m ON g.wishlist_id = m.wishlist_id
            $customerQuery
            ORDER BY g.updated_at desc";

            $query = "SELECT g.wishlist_id, g.customer_id, g.visibility, g.customer_id,
            g.cover, g.name, g.updated_at,
            COUNT(m.wishlist_id) AS totalItem
            FROM ".$resource->getTableName('wishlist/wishlist')." AS g
            LEFT JOIN ".$resource->getTableName('wishlist/item')." AS m ON g.wishlist_id = m.wishlist_id
            $customerQuery
            ORDER BY g.updated_at desc
            LIMIT $offset, $limit;";

            $countWishlists = $readConnection->fetchAll($queryTotal);
            $wishlistCollection = $readConnection->fetchAll($query);

            if ($wishlistCollection) {

                $result[0]['total_record'] = count($countWishlists);
                foreach($wishlistCollection as $value) {
                    $result[$value['wishlist_id']] = $value;
                    $result[$value['wishlist_id']]['slug'] = $value['wishlist_id'].'-'.Mage::getModel('catalog/product_url')->formatUrlKey($value['name']);
                    $result[$value['wishlist_id']]['wishlist_collection_items_total'] = $value['totalItem'];
                }

                return $result;
            }
        }

        return FALSE;
    }

    /**
     * public function getWishlistCollection($customerId = null)
     *
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
            $wishlistCollection->setOrder('updated_at', 'desc');
            $result[0]['total_record'] = $wishlistCollection->getSize();
            $this->_pagination($wishlistCollection);
            $hasCollection = $wishlistCollection->count() < 1 ? false : true;

            if ($wishlistCollection->getData() && $hasCollection) {

                foreach($wishlistCollection as $key => $value) {
                    $result[$key] = $value->getData();
                    $result[$key]['slug'] = $value->getId().'-'.Mage::getModel('catalog/product_url')->formatUrlKey($value->getName());
                    $result[$key]['wishlist_collection_items_total'] = $this->getCollectionItemsTotal($value);
                }

                return $result;
            }
        }

        return FALSE;
    }
     *
     */

    public function getCollectionItemsTotal($wishlist)
    {
        $productWishlistCollection = Mage::getResourceModel('wishlist/item_apicollection_item')
            ->addWishlistFilter($wishlist)
            ->setOrder('added_at', 'desc')
            ->setVisibilityFilter();

        return $productWishlistCollection->getSize();
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
     * additional paramters:
     * ?product_id=62978
     */
    public function deleteWishlistCollectionItem($userid, $wlid, $proid)
    {
        try {
            $w = Mage::getSingleton('core/resource')->getConnection('core_write');
            $result = $w->query('DELETE FROM wishlist_item WHERE product_id ='.$proid.' and wishlist_id IN (select wishlist_id from wishlist where customer_id='.$userid.' and wishlist_id='.$wlid.')');

            return $result;

        } catch (Exception $e) {
            $this->_critical($e->getMessage());
        }

        return FALSE;
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
}
