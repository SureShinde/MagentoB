<?php
/**
 * Description of Bilna_Rest_Model_Api2_Product_Review_Rest
 *
 * @author Bilna Development Team <development@bilna.com>
 */

abstract class Bilna_Rest_Model_Api2_Product_Review_Rest extends Bilna_Rest_Model_Api2_Product_Rest {
    protected function _createValidator($data) {
        $validator = Mage::getModel('bilna_rest/api2_product_validator_review');
        
        if (!$validator->isValidData($data)) {
            foreach ($validator->getErrors() as $error) {
                $this->_error($error, Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
            }
            
            $this->_critical(self::RESOURCE_DATA_PRE_VALIDATION_ERROR);
        }
    }
}
