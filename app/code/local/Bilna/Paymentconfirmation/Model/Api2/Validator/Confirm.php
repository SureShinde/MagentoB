<?php
/**
 * Description of Bilna_Paymentconfirmation_Model_Api2_Validator_Confirm
 *
 * @path    app/code/local/Bilna/Paymentconfirmation/Model/Api2/Validator/Confirm.php
 * @author  Bilna Development Team <development@bilna.com>
 */

class Bilna_Paymentconfirmation_Model_Api2_Validator_Confirm extends Mage_Api2_Model_Resource_Validator {
    public function isValidData(array $data) {
        try {
            $this->_validateOrderNumber($data);
            $this->_validateEmail($data);
            $this->_validateNominal($data);
            $this->_validateBankTo($data);
            $this->_validateTransferDate($data);
            $this->_validateBankFrom($data);
            $this->_validateNameFrom($data);
            $isSatisfied = count($this->getErrors()) == 0;
        }
        catch (Mage_Api2_Exception $e) {
            $this->_addError($e->getMessage());
            $isSatisfied = false;
        }
        
        return $isSatisfied;
    }
    
    protected function _validateOrderNumber($data) {
        if (!isset ($data['order_number']) || empty ($data['order_number'])) {
            $this->_addError('Missing "order_number" in request.', Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        }
    }
    
    protected function _validateEmail($data) {
        if (!isset ($data['email']) || empty ($data['email'])) {
            $this->_addError('Missing "email" in request.', Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        }
    }
    
    protected function _validateNominal($data) {
        if (!isset ($data['nominal']) || empty ($data['nominal'])) {
            $this->_addError('Missing "nominal" in request.', Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        }
    }
    
    protected function _validateBankTo($data) {
        if (!isset ($data['bank_to']) || empty ($data['bank_to'])) {
            $this->_addError('Missing "bank_to" in request.', Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        }
    }
    
    protected function _validateTransferDate($data) {
        if (!isset ($data['transfer_date']) || empty ($data['transfer_date'])) {
            $this->_addError('Missing "transfer_date" in request.', Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        }
    }
    
    protected function _validateBankFrom($data) {
        if (!isset ($data['bank_from']) || empty ($data['bank_from'])) {
            $this->_addError('Missing "bank_from" in request.', Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        }
    }
    
    protected function _validateNameFrom($data) {
        if (!isset ($data['name_from']) || empty ($data['name_from'])) {
            $this->_addError('Missing "name_from" in request.', Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        }
    }
}
