<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Rest
 *
 * @author bilnadev04
 */
abstract class Bilna_Customer_Model_Api2_Customer_Reviewdetail_Rest extends Bilna_Customer_Model_Api2_Customer_Reviewdetail
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
        $customerId = $this->getRequest()->getParam('customer_id');
        
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
