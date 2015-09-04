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
    
    protected function _stripTags($data, $allowableTags = null, $allowHtmlEntities = false) {
        return Mage::helper('core')->stripTags($data, $allowableTags, $allowHtmlEntities);
    }

    protected function parserPath($path) {
        return str_replace('.html', '', $path);
    }

    protected function getBrandId($path) {
        return Mage::getModel('brands/brands')->checkIdentifier($path, 1);
    }
}