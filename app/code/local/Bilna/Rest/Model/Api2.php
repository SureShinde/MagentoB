<?php
/**
 * Description of Bilna_Rest_Model_Api2
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Rest_Model_Api2 extends Mage_Api2_Model_Resource {
    const DEFAULT_STORE_ID = 1;
    
    protected $_data = array ();
    
    protected function _getStore() {
        return Mage::getModel('core/store')->load(self::DEFAULT_STORE_ID);
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
}
