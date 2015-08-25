<?php
/**
 * Description of Bilna_Rest_Model_Api2_Productsearch_Rest
 *
 * @author Bilna Development Team <development@bilna.com>
 */

abstract class Bilna_Rest_Model_Api2_Productsearch_Rest extends Bilna_Rest_Model_Api2_Productsearch {
    protected $_query = null;
    protected $_queryText = null;
    protected $_isMaxLength = false;
    
    protected function _getQuery() {
        if (!$this->_query) {
            $this->_query = Mage::getModel('catalogsearch/query')->loadByQuery($this->_getQueryText());
            
            if (!$this->_query->getId()) {
                $this->_query->setQueryText($this->_getQueryText());
            }
        }
        
        return $this->_query;
    }
    
    protected function _getQueryText() {
        if (!isset ($this->_queryText)) {
            $this->_queryText = $this->getRequest()->getQuery($this->_getQueryParamName());
            
            if ($this->_queryText === null) {
                $this->_queryText = '';
            }
            else {
                /* @var $stringHelper Mage_Core_Helper_String */
                $stringHelper = Mage::helper('core/string');
                $this->_queryText = is_array($this->_queryText) ? '' : $stringHelper->cleanString(trim($this->_queryText));
                $maxQueryLength = $this->_getMaxQueryLength($this->_getStore());
                
                if ($maxQueryLength !== '' && $stringHelper->strlen($this->_queryText) > $maxQueryLength) {
                    $this->_queryText = $stringHelper->substr($this->_queryText, 0, $maxQueryLength);
                    $this->_isMaxLength = true;
                }
            }
        }
        
        return $this->_queryText;
    }
    
    protected function _getQueryParamName() {
        return Mage_CatalogSearch_Helper_Data::QUERY_VAR_NAME;
    }
    
    protected function _getMaxQueryLength($store = null) {
        return Mage::getStoreConfig(Mage_CatalogSearch_Model_Query::XML_PATH_MAX_QUERY_LENGTH, $store);
    }
    
    protected function _isMinQueryLength() {
        $minQueryLength = $this->_getMinQueryLength($this->_getStore());
        $thisQueryLength = Mage::helper('core/string')->strlen($this->_getQueryText());
        
        return !$thisQueryLength || $minQueryLength !== '' && $thisQueryLength < $minQueryLength;
    }
    
    protected function _getMinQueryLength($store = null) {
        return Mage::getStoreConfig(Mage_CatalogSearch_Model_Query::XML_PATH_MIN_QUERY_LENGTH, $store);
    }
}
