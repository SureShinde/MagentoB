<?php
/**
 * Description of Bilna_Rest_Model_Api2_Validator_Newsletter
 *
 * @path    app/code/local/Bilna/Rest/Model/Api2/Validator/Newsletter.php
 * @author  Bilna Development Team <development@bilna.com>
 */

class Bilna_Rest_Model_Api2_Validator_Newsletter extends Mage_Api2_Model_Resource_Validator {
    public function isValidData(array $data) {
        try {
            $this->_validateEmail($data);
            $this->_validateType($data);
            $isSatisfied = count($this->getErrors()) == 0;
        }
        catch (Mage_Api2_Exception $e) {
            $this->_addError($e->getMessage());
            $isSatisfied = false;
        }
        
        return $isSatisfied;
    }
    
    protected function _validateEmail($data) {
        if (!isset ($data['email']) || empty ($data['email'])) {
            $this->_addError('Missing "email" in request.', Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        }
    }
    
    protected function _validateType($data) {
        if (!isset ($data['type']) || empty ($data['type'])) {
            $this->_addError('Missing "type" in request.', Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        }
        
        if (!in_array($data['type'], ['subscribe', 'confirmation', 'unsubscribe'])) {
            $this->_addError('field "type" is not valid.', Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        }
    }
}
