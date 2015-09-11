<?php
/**
 * Description of Bilna_Rest_Model_Api2_Navigation_Rest_Admin_V1
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Rest_Model_Api2_Navigation_Rest_Admin_V1 extends Bilna_Rest_Model_Api2_Navigation_Rest {
    protected function _retrieve() {
        $_params = $this->_getParams();
        $this->_setRegistry();
        
        $_categoryId = $_params['category_id'];
        $_category = $this->_getCategory($_categoryId);
        $_attribute = $this->_getAttribute($_category);
        $_subcategory = $this->_getSubcategory($_category);
        
        $_result = array (
            'subcategory' => $_subcategory,
            'attribute' => $_attribute,
        );
        
        $this->_unsetRegistry();
        
        return $_result;
    }
}
