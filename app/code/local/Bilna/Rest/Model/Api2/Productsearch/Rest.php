<?php
/**
 * Description of Bilna_Rest_Model_Api2_Productsearch_Rest
 *
 * @author Bilna Development Team <development@bilna.com>
 */

abstract class Bilna_Rest_Model_Api2_Productsearch_Rest extends Bilna_Rest_Model_Api2_Productsearch {
    protected $_storeId = null;
    protected $_queryText = null;
    protected $_isMaxLength = false;
    
    protected function _getParams() {
        $this->_storeId = self::DEFAULT_STORE_ID;
        $this->_queryText = $this->_getQueryText();
    }
    
    protected function _getQueryText() {
        $_queryText = $this->getRequest()->getQuery('query_text');

        if ($_queryText === null) {
            $this->_critical(self::RESOURCE_REQUEST_DATA_INVALID);
        }
        
        /* @var $stringHelper Mage_Core_Helper_String */
        $stringHelper = Mage::helper('core/string');
        $_queryText = is_array($_queryText) ? '' : $stringHelper->cleanString(trim($_queryText));
        $maxQueryLength = $this->_getMaxQueryLength($this->_getStore());

        if ($maxQueryLength !== '' && $stringHelper->strlen($_queryText) > $maxQueryLength) {
            $this->_queryText = $stringHelper->substr($_queryText, 0, $maxQueryLength);
            $this->_isMaxLength = true;
        }
        
        return $_queryText;
    }
    
    protected function _getResultProducts() {
        $_result = array ();
        
        if (JeroenVermeulen_Solarium_Model_Engine::isEnabled($this->_storeId)) {
            $_solariumEngine = Mage::getSingleton('jeroenvermeulen_solarium/engine');
            
            if ($_solariumEngine->isWorking()) {
                $_searchResult = $_solariumEngine->search($this->_storeId, $this->_queryText);
                $_searchResult->setUserQuery($this->_queryText);
                
                if (!$_searchResult->getResultCount()) {
                    // Autocorrect
                    if ($_solariumEngine->getConf('results/autocorrect', $this->_storeId)) {
                        $_searchResult->autoCorrect();
                    }
                }
                
                $_result = $this->_parsingResultProducts($_searchResult->getResultProducts());
            }
        }
        
        return $_result;
    }
    
    protected function _parsingResultProducts($resultProducts) {
        $_result = array ();
        
        if (count($resultProducts) > 0) {
            foreach ($resultProducts as $row) {
                $_result[] = $row['product_id'];
            }
        }
        
        return $_result;
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
