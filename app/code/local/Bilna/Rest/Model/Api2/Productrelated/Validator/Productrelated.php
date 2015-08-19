<?php
/**
 * Description of Bilna_Rest_Model_Api2_Productrelated_Validator_Productrelated
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Rest_Model_Api2_Productrelated_Validator_Productrelated extends Mage_Api2_Model_Resource_Validator {
    public function isValidData(array $data) {
        if (!isset ($data['name']) || !$data['name']) {
            $this->_addError('The name is not specified');
        }
        
        if (!isset ($data['customer_group_id']) || !$data['customer_group_id']) {
            $this->_addError('The customer_group_id is not specified');
        }
        
        if (!isset ($data['product_id']) || !$data['product_id']) {
            $this->_addError('The product_id is not specified');
        }

        return !count($this->getErrors());
    }
}
