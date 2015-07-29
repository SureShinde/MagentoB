<?php
/**
 * Description of Bilna_Rest_Model_Api2_Product_Image_Validator_Image
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Rest_Model_Api2_ProductAlert_Validator_ProductAlert extends Mage_Api2_Model_Resource_Validator {
    /**
     * Validate data. In case of validation failure return false,
     * getErrors() could be used to retrieve list of validation error messages
     *
     * @param array $data
     * @return bool
     */
    public function isValidData(array $data) {
        if (!isset ($data['product_id'])) {
            $this->_addError('The product_id is not specified');
        }
        
        if (!isset ($data['customer_id'])) {
            $this->_addError('The customer_id is not specified');
        }
        
        if (!isset ($data['email'])) {
            $this->_addError('The email is not specified');
        }

        return !count($this->getErrors());
    }
}