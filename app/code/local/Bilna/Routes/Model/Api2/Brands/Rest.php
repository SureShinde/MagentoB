<?php
/**
 * Description of Bilna_Routes_Model_Api2_Brands_Rest
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Routes_Model_Api2_Brands_Rest extends Bilna_Routes_Model_Api2_Brands {
    protected function _getBrandsCollection() {
        $collection = Mage::getModel('brands/brands')->getCollection();
        $collection->addAttributeToSelect(array ('title', 'description', 'url_key', 'image'))
            ->addAttributeToFilter('active', 1)
            ->setStore($this->_getStore())
            ->setOrder('title', 'ASC');
        
        return $collection;
    }
    
    protected function _getProductCollection($_brandsId) {
        $_result = array ();
        $_collection = Mage::getResourceModel('catalog/product_collection')
            ->addAttributeToSelect('entity_id')
            ->addFieldToFilter(array (array ('attribute' => 'brands', 'eq' => $_brandsId)))
            ->addStoreFilter(self::DEFAULT_STORE_ID);
        
        if ($_collection->getSize()) {
            foreach ($_collection as $_product) {
                $_result[] = $_product->getId();
            }
        }
        
        return $_result;
    }
    
    protected function _stripTags($data, $allowableTags = null, $allowHtmlEntities = false) {
        return Mage::helper('core')->stripTags($data, $allowableTags, $allowHtmlEntities);
    }

    protected function parserPath($path) {
        return str_replace('.html', '', $path);
    }

    protected function getBrandsId($path) {
        return Mage::getModel('brands/brands')->checkIdentifier($path, 1);
    }
}
