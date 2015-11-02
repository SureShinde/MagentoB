<?php
/**
 * Description of Rest
 *
 * @author Bilna Development Team <development@bilna.com>
 */
abstract class Bilna_Customer_Model_Api2_Customer_Review_Rest extends Bilna_Customer_Model_Api2_Customer_Review
{
    /**
     *
     */
    protected function _getCustomer($customerId)
    {
        /** @var $customer Mage_Customer_Model_Customer */
        $customer = Mage::getModel('customer/customer')
            ->load($customerId);
        if (!$customer->getId()) {
            throw Mage::throwException('Customer Not Exists');
        }
        return $customerId;
    }
    
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
