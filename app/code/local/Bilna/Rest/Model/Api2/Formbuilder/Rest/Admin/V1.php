<?php
/**
 * Description of Bilna_Rest_Model_Api2_Formbuilder_Rest_Admin_V1
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Rest_Model_Api2_Formbuilder_Rest_Admin_V1 extends Bilna_Rest_Model_Api2_Formbuilder_Rest {
    protected function _retrieve() {
        $_result = array ();
        $_formId = $this->getRequest()->getParam('id');
        $_form = $this->_getForm($_formId);
        
        $_result = $_form;
        $_result['inputs'] = $this->_getInputs($_formId);
        
        return $_result;
    }
}
