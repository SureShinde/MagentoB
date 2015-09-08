<?php
/**
 * Description of Bilna_Rest_Model_Api2_Navigation_Rest_Admin_V1
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Rest_Model_Api2_Navigation_Rest_Admin_V1 extends Bilna_Rest_Model_Api2_Navigation_Rest {
    protected function _retrieve() {
        $_params = $this->_getParams();
        $_categoryId = $_params['category_id'];
        $_category = $this->_getCategory($_categoryId);
        
        $_result = array ();
        $_result['subcategory'] = $this->_getSubcategory($_category);
        $_result['layer'] = $this->_getLayer($_category);
        
        return $_result;
    }
}
