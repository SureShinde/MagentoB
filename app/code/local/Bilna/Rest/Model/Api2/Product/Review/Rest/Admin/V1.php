<?php
/**
 * Description of Bilna_Rest_Model_Api2_Product_Review_Rest_Admin_V1
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Rest_Model_Api2_Product_Review_Rest_Admin_V1 extends Bilna_Rest_Model_Api2_Product_Review_Rest {
    protected function _create(array $data) {
        $this->_createValidator($data);
        
        $product = $this->_getProduct();
        $customerId = $data['customer_id'];
        $ratings = $data['ratings'];
        
        if (!$product) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        
        try {
            $review = Mage::getModel('review/review')->setData($data);
            $review->setEntityId($review->getEntityIdByCode(Mage_Review_Model_Review::ENTITY_PRODUCT_CODE))
                ->setEntityPkValue($product->getId())
                ->setStatusId(Mage_Review_Model_Review::STATUS_PENDING)
                ->setCustomerId($customerId)
                ->setStoreId($this->_getStore()->getId())
                ->setStores(array ($this->_getStore()->getId()))
                ->save();
            
            foreach ($ratings as $ratingId => $optionId) {
                Mage::getModel('rating/rating')
                    ->setRatingId($ratingId)
                    ->setReviewId($review->getId())
                    ->setCustomerId($customerId)
                    ->addOptionVote($optionId, $product->getId());
            }

            $review->aggregate();
        }
        catch (Exception $e) {
            $this->_critical($e->getMessage());
        }
        
        $this->_getLocation($review);
    }

    protected function _retrieveCollection() {
        $collection = Mage::getModel('review/review')->getResourceCollection()
            ->addStoreFilter($this->_getStore()->getId())
            ->addEntityFilter('product', $this->_getProduct()->getId())
            ->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED)
            ->setDateOrder();
        
        $this->_applyCollectionModifiers($collection);
        $reviews = $collection->load();
        
        if ($reviews->getSize() == 0) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        
        $result = array ();
        $result['totalRecord'] = $reviews->getSize();
        
        foreach ($reviews->getItems() as $review) {
            $votes = array ();
            $rating = Mage::getModel('rating/rating_option_vote')
                ->getResourceCollection()
                ->addFieldToSelect(array ('percent'))
                ->setReviewFilter($review->getId())
                ->setStoreFilter($this->_getStore()->getId())
                ->load();
            
            if ($rating->getSize()) {
                $ratingData = $rating->getData();
                $votes['rating_code'] = 'Product Rating';
                $votes['percent'] = $ratingData[0]['percent'];
            }
            
            $result[$review->getId()] = array (
                'nickname' => $review->getNickname(),
                'title' => $review->getTitle(),
                'detail' => $review->getDetail(),
                'created_at' => $review->getCreatedAt(),
                'votes' => $votes,
            );
        }
        
        return $result;
    }
}
