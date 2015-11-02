<?php
/**
 * Description of V1
 *
 * @author Bilna Development Team <development@bilna.com>
 */
class Bilna_Customer_Model_Api2_Customer_Review_Rest_Admin_V1 extends Bilna_Customer_Model_Api2_Customer_Review_Rest
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
        $customerId = $this->_getCustomer($this->getRequest()->getParam('customer_id'));
        
        $reviewsCollection = Mage::getModel('review/review')->getCollection()
                ->addCustomerFilter($customerId)
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
                    'product_name' => $product['name'], 
                    'product_url_path' => $product['url_path']
                );
            }
        }
        
        return $response;
    }
}
