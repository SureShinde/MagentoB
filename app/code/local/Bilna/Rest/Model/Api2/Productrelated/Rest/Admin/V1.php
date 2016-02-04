<?php
/**
 * Description of Bilna_Rest_Model_Api2_Productrelated_Rest_Admin_V1
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Rest_Model_Api2_Productrelated_Rest_Admin_V1 extends Bilna_Rest_Model_Api2_Productrelated_Rest {
    protected function _retrieve() {
        $this->_getParams();
        
        $block = $this->_getBlock();
        $product = $this->_getProduct();
        $collection = $this->_getCollection();
        $response = $this->_parseResponse($block, $collection);
        
        return $response;
    }
}
