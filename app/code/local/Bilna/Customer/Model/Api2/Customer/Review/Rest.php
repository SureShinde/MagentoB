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
abstract class Bilna_Customer_Model_Api2_Customer_Review_Rest extends Bilna_Customer_Model_Api2_Customer_Review
{
    /**
     * Retrieve collection review based on customer id from table:
     * - review (FYI: entity_pk_value is product id relation)
     * - review_detail
     *
     * @return array
     */
    protected function _retrieveCollection() 
    {
        $reviewsCollection = Mage::getModel('review/review')->getCollection()
                ->addCustomerFilter($this->getRequest()->getParam('customer_id'))
                ->addStoreFilter(Mage::app()->getStore()->getId())
                ->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED)
                ->setDateOrder();
        
        $review = $reviewsCollection->getData();
        $response = array();
        if(!empty($review)) {
            foreach($review as $item) {
                $productHelper = Mage::helper('catalog/product');
                $product = $productHelper->getProduct($item['entity_pk_value'], Mage::app()->getStore()->getId());
                
                $response[] = array(
                    'review_id' => $item['review_id'], 
                    'created_at' => $item['created_at'], 
                    'product_id' => $product['entity_id'], 
                    'product_name' => $product['name']
                );
            }
        }
        
        return $response;
    }
}
