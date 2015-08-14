<?php
/**
 * Description of Bilna_Rest_Model_Api2_Product_Validator_Review
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Rest_Model_Api2_Product_Validator_Review extends Mage_Api2_Model_Resource_Validator {
    /**
     * Validate data. In case of validation failure return false,
     * getErrors() could be used to retrieve list of validation error messages
     *
     * @param array $data
     * @return bool
     */
    public function isValidData(array $data) {
        if (!isset ($data['nickname'])) {
            $this->_addError('The nickname is not specified');
        }
        
        if (!isset ($data['email'])) {
            $this->_addError('The email is not specified');
        }
        
        if (!isset ($data['customer_id']) || !($data['customer_id'])) {
            $this->_addError('The customer_id is not specified');
        }
        
        if (!isset ($data['summary'])) {
            $this->_addError('The summary is not specified');
        }
        
        if (!isset ($data['review'])) {
            $this->_addError('The review is not specified');
        }
        
        if (!isset ($data['ratings'])) {
            $this->_addError('The ratings is not specified');
        }

        return !count($this->getErrors());
    }
}
