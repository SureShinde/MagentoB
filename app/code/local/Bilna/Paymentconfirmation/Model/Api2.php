<?php
/**
 * Description of Bilna_Paymentconfirmation_Model_Api2
 *
 * @path    app/code/local/Bilna/Paymentconfirmation/Model/Api2.php
 * @author  Bilna Development Team <development@bilna.com>
 */

class Bilna_Paymentconfirmation_Model_Api2 extends Mage_Api2_Model_Resource {
    const DEFAULT_STORE_ID = 1;
    
    public function __construct() {
        Mage::app()->getStore()->setStoreId(self::DEFAULT_STORE_ID);
    }
    
    protected function _getStore() {
        return Mage::app()->getStore();
    }
    
    protected function _createValidator($validatorModel) {
        $validator = Mage::getModel($validatorModel);
        
        if ($this->_data) {
            if (!$validator->isValidData($this->_data)) {
                foreach ($validator->getErrors() as $error) {
                    $this->_error($error, Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
                }

                $this->_critical(self::RESOURCE_DATA_PRE_VALIDATION_ERROR);
            }
        }
    }
    
    protected function _isJson($response) {
        json_decode($response);
        
        return (json_last_error() == JSON_ERROR_NONE);
    }
}
