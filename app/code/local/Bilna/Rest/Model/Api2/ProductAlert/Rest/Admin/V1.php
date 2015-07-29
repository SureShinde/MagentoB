<?php
/**
 * Description of Bilna_Rest_Model_Api2_ProductAlert_Rest_Admin_V1
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Rest_Model_Api2_ProductAlert_Rest_Admin_V1 extends Bilna_Rest_Model_Api2_ProductAlert_Rest {
    protected function _create(array $data) {
        $this->_createValidator($data);
        
        $productId = $data['product_id'];
        $product = Mage::getModel('catalog/product')->load($productId);
        
        if (!$product) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        
        $email = $data['email'];
        $customerId = $data['customer_id'];
        $websiteId = $this->_getStore()->getWebsiteId();
        
        if ($customerId == 0) {
            $customer = Mage::getModel('customer/customer')->setWebsiteId($websiteId)->loadByEmail($email);
            
            if ($customer->getId()) {
                $customerId = $customer->getId();
            }
        }
        
        $checkEmail = Mage::getModel('productalert/stock')->getCollection();
        $checkEmail->addFieldToFilter('email', $email)->addFieldToFilter('product_id', $productId);
        
        if ($checkEmail->getSize()) {
            $resource = $checkEmail->getFirstItem();
        }
        else {
            try {
                $resource = Mage::getModel('productalert/stock')
                    ->setCustomerId($customerId)
                    ->setEmail($email)
                    ->setProductId($productId)
                    ->setWebsiteId($websiteId);
                $resource->save();
            }
            catch (Exception $e) {
                $this->_critical($e->getMessage());
            }
        }
        
        $this->_getLocation($resource);
    }
    
    protected function _createValidator($data) {
        /* @var $validator Mage_Catalog_Model_Api2_Product_Image_Validator_Image */
        $validator = Mage::getModel('bilna_rest/api2_productalert_validator_productalert');
        
        if (!$validator->isValidData($data)) {
            foreach ($validator->getErrors() as $error) {
                $this->_error($error, Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
            }
            
            $this->_critical(self::RESOURCE_DATA_PRE_VALIDATION_ERROR);
        }
    }
    
    protected function _retrieve() {
        $alertId = $this->getRequest()->getParam('id');
        $collection = Mage::getModel('productalert/stock')->getResourceCollection();
        $collection->addFieldToFilter('alert_stock_id', $alertId);
        $productAlertStock = $collection->getFirstItem()->getData();
        
        if (!$productAlertStock) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        
        return $productAlertStock;
    }
}
