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
    
    protected function _getProductCollection($brandsId) {
        $_storeId = $this->_getStore()->getId();
        $_collection = Mage::getResourceModel('catalog/product_collection')
            ->addAttributeToSelect($this->_attributeProductCollection)
            ->addFieldToFilter(array (array ('attribute' => 'brands', 'eq' => $brandsId)))
            ->addStoreFilter($_storeId);
        $this->_applyCollectionModifiers($_collection);
        $_products = Mage::helper('bilna_rest/api2')->retrieveCollectionResponse($_collection->load(), $this->_attributeProductCollection);
        
        return $_products;
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
