<?php
/**
 * Description of Bilna_Rest_Model_Api2_Productsearch_Rest
 *
 * @author Bilna Development Team <development@bilna.com>
 */

abstract class Bilna_Rest_Model_Api2_Productsearch_Rest extends Bilna_Rest_Model_Api2_Productsearch {
    protected function _getProductCollection($_queryText, Mage_CatalogSearch_Model_Query $_query) {
        $_result = array ();
        $_storeId = $_query->getStoreId();
        
        if (JeroenVermeulen_Solarium_Model_Engine::isEnabled($_storeId)) {
            $_helperSolarium = Mage::helper('jeroenvermeulen_solarium');
            $_helperCatSearch = Mage::helper('catalogsearch');
            $_engine = Mage::getSingleton('jeroenvermeulen_solarium/engine'); /** @var JeroenVermeulen_Solarium_Model_Engine $engine */
            
            //- $_adapter = $this->_getWriteAdapter();
            //- $searchResultTable = $this->getTable('catalogsearch/result');
            
            if ($_engine->isWorking()) {
                $_searchResult = $_engine->search($_storeId, $_queryText);
                $_searchResult->setUserQuery($_queryText);
                //Mage::register('solarium_search_result', $_searchResult, true);
                
                if (!$_searchResult->getResultCount()) {
                    // Autocorrect
                    if ($_engine->getConf('results/autocorrect', $_storeId)) {
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
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    protected $_query = null;
    protected $_queryText = null;
    protected $_isMaxLength = false;
    
    protected function _getQuery() {
        $_queryText = $this->_getQueryText();
        $modelCatalogsearchQuery = Mage::getModel('catalogsearch/query');
        $modelCatalogsearchQuery->setStoreId($this->_getStore()->getId());
        $_query = $modelCatalogsearchQuery->loadByQuery($_queryText);
            
        if (!$_query->getId()) {
            $_query->setQueryText($_queryText);
        }
        
        return $_query;
    }
    
    protected function _getQueryText() {
        $_queryText = $this->getRequest()->getQuery($this->_getQueryParamName());

        if ($_queryText === null) {
            $_queryText = '';
        }
        else {
            /* @var $stringHelper Mage_Core_Helper_String */
            $stringHelper = Mage::helper('core/string');
            $_queryText = is_array($this->_queryText) ? '' : $stringHelper->cleanString(trim($_queryText));
            $maxQueryLength = $this->_getMaxQueryLength($this->_getStore());

            if ($maxQueryLength !== '' && $stringHelper->strlen($_queryText) > $maxQueryLength) {
                $this->_queryText = $stringHelper->substr($_queryText, 0, $maxQueryLength);
                $this->_isMaxLength = true;
            }
        }
        
        return $_queryText;
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
    
//    protected function _getProductCollection() {
//        $_collection = $this->_getCurrentCategory()->getProductCollection();
//        $_collection->printLogQuery(true);exit;
        
        
//        $this->prepareProductCollection($collection);
//        $this->_productCollections[$this->getCurrentCategory()->getId()] = $collection;

//        return $collection;
//    }
    
    protected function _getCurrentCategory() {
        $_modelCatalogCategory = Mage::getModel('catalog/category');
        $_modelCatalogCategory->setStoreId($this->_getStore()->getId());
        $_category = $_modelCatalogCategory->load($this->_getStore()->getRootCategoryId());

        return $_category;
    }
}
