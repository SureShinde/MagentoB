<?php
/**
 * Description of Bilna_Rest_Model_Api2_Productfeatured_Rest_Admin_V1
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Rest_Model_Api2_Productfeatured_Rest_Admin_V1 extends Bilna_Rest_Model_Api2_Productfeatured_Rest {
    protected function _retrieve() {
        $this->_getParams();
        
        $block = $this->_getBlock();
        $blockData = $this->_parseBlock($block->getData());
        
        return $blockData;
    }
}
