<?php
/**
 * Description of Bilna_Rest_Model_Api2_Productsearch_Rest_Admin_V1
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Rest_Model_Api2_Productsearch_Rest_Admin_V1 extends Bilna_Rest_Model_Api2_Productsearch_Rest {
    protected function _retrieve() {
        $_query = $this->_getQuery();
        
        if ($_query->getQueryText() == '') {
            $this->_critical(self::RESOURCE_REQUEST_DATA_INVALID);
        }
        
        $_result = $_query->getData();
        
        if (Mage::helper('catalogsearch')->isMinQueryLength()) {
            $_query->setId(0)->setIsActive(1)->setIsProcessed(1);
        }
        else {
            if ($_query->getId()) {
                $_query->setPopularity($_query->getPopularity() + 1);
            }
            else {
                $_query->setPopularity(1);
            }

            if ($_query->getRedirect()) {
                $_query->save();
            }
            else {
                $_query->prepare();
                $_result['products'] = $this->_getProductCollection($this->_getQueryText(), $_query);
            }
        }
        
        return $_result;
    }
}
