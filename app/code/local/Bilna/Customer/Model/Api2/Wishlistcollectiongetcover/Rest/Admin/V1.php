<?php
/**
 * Description of Bilna_Customer_Model_Api2_Wishlistcollection_Rest_Admin_V1
 *
 * @author Bilna Development Team <development@bilna.com>
 */
class Bilna_Customer_Model_Api2_Wishlistcollectiongetcover_Rest_Admin_V1 extends Bilna_Customer_Model_Api2_Wishlistcollectiongetcover_Rest
{
    protected function _retrieve()
    {
        $username = $this->getRequest()->getParam('username');
        $categoryId = $this->getRequest()->getParam('category_id');
        
        $profiler = Mage::getModel('socialcommerce/profile')->load($username, 'username');
        if (!$profiler->getCustomerId()) {
            $this->_critical('Current username is not found.');
        }
        $customerId = $profiler->getCustomerId();
        
        $customer = $this->_loadCustomerById($customerId);
        
        if ($customer->getId()) {
            try {
                $images = Mage::getModel('socialcommerce/collectioncover')->getCollection();
                $images->addFieldToFilter('category_id', $categoryId);
                return ['response' => $images->getData()];
            } catch (Exception $e) {
                $this->_critical($e->getMessage());
            }
        } else {
            $this->_critical('No customer account specified.');
        }
    }
}
