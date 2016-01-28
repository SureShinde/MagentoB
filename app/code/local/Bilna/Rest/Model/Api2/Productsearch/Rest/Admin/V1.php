<?php
/**
 * Description of Bilna_Rest_Model_Api2_Productsearch_Rest_Admin_V1
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Rest_Model_Api2_Productsearch_Rest_Admin_V1 extends Bilna_Rest_Model_Api2_Productsearch_Rest {
    protected function _retrieve() {
        $this->_getParams();
        
        $_resultProducts = $this->_getResultProducts();
        
        if (count($_resultProducts) == 0) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        
        $_result = array (
            'query_text' => $this->_queryText,
            'total_record' => count($_resultProducts),
            'products' => $_resultProducts,
        );
        
        return $_result;
    }
}
