<?php
/**
 * Description of Bilna_Rest_Model_Api2_Product_Review_Rest_Admin_V1
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Rest_Model_Api2_Product_Review_Rest_Admin_V1 extends Bilna_Rest_Model_Api2_Product_Review_Rest {
    protected function _retrieveCollection() {
        $collection = Mage::getModel('review/review')->getResourceCollection()
            ->addStoreFilter($this->_getStore()->getId())
            ->addEntityFilter('product', $this->_getProduct()->getId())
            ->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED)
            ->setDateOrder();
        
        if ($collection->getSize() == 0) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        
        $result = array ();
        
        foreach ($collection->getItems() as $review) {
            $ratingVotes = $review->getRatingVotes();
            $votes = array ();
            
            if (count($ratingVotes) > 0) {
                foreach ($ratingVotes as $ratingVote) {
                    $votes['rating_code'] = $ratingVote->getRatingCode();
                    $votes['percent'] = $ratingVote->getPercent();
                }
            }
            
            $result[] = array (
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
