<?php
/**
 * Description of Bilna_Rest_Model_Api2
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Rest_Model_Api2 extends Mage_Api2_Model_Resource {
    const DEFAULT_STORE_ID = 1;
    
    protected $_data = array ();
    
    public function __construct() {
        Mage::app()->getStore()->setStoreId(self::DEFAULT_STORE_ID);
    }
    
    protected function _getStore() {
        return Mage::app()->getStore();
    }
    
    protected function _createValidator($validatorModel) {
        $validator = Mage::getModel($validatorModel);
        
        if ($this->_data) {
            if (!$validator->isValidData($this->_data)) {
                foreach ($validator->getErrors() as $error) {
                    $this->_error($error, Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
                }

                $this->_critical(self::RESOURCE_DATA_PRE_VALIDATION_ERROR);
            }
        }
    }
    
    protected function _getCurrentDate() {
        return Mage::getModel('core/date')->date('Y-m-d');
    }
    
    protected $_cache;
    protected $_cacheKey;

    protected function _initCache() {
        $this->_cache = Mage::getSingleton('core/cache');
    }
    
    protected function _getCacheKey() {
        $currentUrl = Mage::helper('core/url')->getCurrentUrl();
        $url = Mage::getSingleton('core/url')->parseUrl($currentUrl);
        $path = $url->getPath();
        $query = $url->getQuery();
        
        return sprintf("%s?%s", $path, $query);
    }

    protected function _getCacheData($key) {
        if ($response = $this->_cache->load($key)) {
            
            if ($this->_isJson($response)) {
                $response = json_decode($response, true);
            }
            
            return $response;
        }
        
        return false;
    }
    
    protected function _setCacheData($response, $key) {
        if (is_array($response)) {
            $response = json_encode($response);
        }
        
        try {
            $tags = array (
                Mage_Catalog_Model_Product::CACHE_TAG,
                Mage_Catalog_Model_Product::CACHE_TAG . $key,
            );
            $lifetime = Mage::getStoreConfig('core/cache/lifetime');
        
            $this->_cache->save($response, $key, $tags, $lifetime);
        }
        catch (Exception $ex) {
            Mage::logException($e);
            $this->_critical(self::RESOURCE_INTERNAL_ERROR);
        }
    }
    
    protected function _isJson($response) {
        json_decode($response);
        
        return (json_last_error() == JSON_ERROR_NONE);
    }
}
