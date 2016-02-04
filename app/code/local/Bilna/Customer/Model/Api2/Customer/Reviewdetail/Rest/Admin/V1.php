<?php
/**
 * Description of V1
 *
 * @author Bilna Development Team <development@bilna.com>
 */
class Bilna_Customer_Model_Api2_Customer_Reviewdetail_Rest_Admin_V1 extends Bilna_Customer_Model_Api2_Customer_Reviewdetail_Rest
{

    /**
     * Retrieve detail of review rating from table: 
     * - review detail
     * - rating_option_vote
     *
     * @return array
     */
    protected function _retrieve()
    {
        $reviewId = $this->getRequest()->getParam('review_id');
        $customerId = $this->_getCustomer($this->getRequest()->getParam('customer_id'));
        
        $reviewRating = $this->getRatingOptionRate($reviewId);
        $reviewDetail = $this->getReviewDetail($reviewId, $customerId);
        
        $productId = $reviewRating['entity_pk_value'];
        
        $product = $this->getProductDetail($productId);
        
        return array(
            'review_rating' => $reviewRating, 
            'review_detail' => $reviewDetail,
            'product' => $product
        );
    }
}
