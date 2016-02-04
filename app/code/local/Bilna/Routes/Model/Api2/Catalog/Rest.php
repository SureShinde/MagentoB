<?php
/**
 * Description of Bilna_Routes_Model_Api2_Catalog_Rest
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Routes_Model_Api2_Catalog_Rest extends Bilna_Routes_Model_Api2_Catalog {
    protected function _retrieve() {
        $path = $this->getRequest()->getParam('path');
        $collection = $this->_loadByRequestPath($path);
        $result = array ();
        
        if ($collection->getSize() == 0) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        
        foreach ($collection as $v) {
            $result = array (
                'id' => $v->getData('url_rewrite_id'),
                'store_id' => $v->getData('store_id'),
                'type' => $this->_getType($v->getData('id_path')),
                'id_path' => $v->getData('id_path'),
                'request_path' => $v->getData('request_path'),
                'target_path' => $v->getData('target_path'),
                'options' => $v->getData('options'),
                'category_id' => $v->getData('category_id'),
                'product_id' => $v->getData('product_id'),
            );
            break;
        }
        
        return $result;
    }
    
    protected function _loadByRequestPath($path) {
        $collection = Mage::getModel('core/url_rewrite')->getCollection();
        $collection->addFieldToFilter('request_path', $path);
        $collection->addFieldToFilter('store_id', 1);
        
        return $collection;
    }
    
    protected function _getType($idPath) {
        $idPathArr = explode('/', $idPath);
        
        return $idPathArr[0];
    }
}