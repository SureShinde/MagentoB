<?php
/**
 * Description of Bilna_Rest_Model_Api2_Formbuilder_Rest_Admin_V1
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Rest_Model_Api2_Formbuilder_Rest_Admin_V1 extends Bilna_Rest_Model_Api2_Formbuilder_Rest {
    protected function _create(array $_filteredData) {
        $_formId = $this->getRequest()->getParam('id');
        $_formData = $_filteredData['data'];
        $_form = $this->_getForm($_formId);
        $_inputs = $this->_getInputs($_formId);
        $_errors = false;
        
        if ($_inputs) {
            foreach ($_inputs as $_input) {
                if (($_error = $this->_validRequired($_input, $_formData)) !== true) {
                    $this->_error($_error, Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
                    $_errors = true;
                    continue;
                }
                
                if (($_error = $this->_validUnique($_formId, $_input, $_formData[$_input['name']])) !== true) {
                    $this->_error($_error, Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
                    $_errors = true;
                    continue;
                }
            }
        }
        
        if ($_errors) {
            $this->_critical(self::RESOURCE_DATA_PRE_VALIDATION_ERROR);
        }
        
        if (!$this->_saveData($_formId, $_form, $_formData)) {
            $this->_critical(self::RESOURCE_INTERNAL_ERROR);
        }
        
        $this->_getLocation($_form);
    }
    
    protected function _retrieve() {
        $_result = array ();
        $_formId = $this->getRequest()->getParam('id');
        $_form = $this->_getForm($_formId);
        
        $_result = $_form->getData();
        $_result['inputs'] = $this->_getInputs($_formId);
        
        return $_result;
    }
}
