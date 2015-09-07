<?php
/**
 * Description of Bilna_Routes_Model_Api2_Brands_Rest_Admin_V1
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Routes_Model_Api2_Brands_Rest_Admin_V1 extends Bilna_Routes_Model_Api2_Brands_Rest {
    protected function _retrieve() {
        $path = $this->parserPath($this->getRequest()->getParam('path'));
        $brandsId = $this->getBrandsId($path);
        $result = array ();
        
        if (!$brandsId) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        
        $brands = Mage::getModel('brands/brands')->load($brandsId);
        
        if (!$brands->getSize() == 0) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        
        $result = $brands->getData();
        $result['products'] = $this->_getProductCollection($brandsId);
        
        return $result;
    }
    
    protected function _retrieveCollection() {
        $_brands = $this->_getBrandsCollection();
        
        if (!$_brands->count()) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        
        $_result = array ();
        $_result[0] = array ('total_record' => $_brands->count());
        
        foreach ($_brands as $_brand) {
            $_image = (string) Mage::helper('brands/image')->init($_brand)->resize(135);
            $_result[$_brand->getId()] = array (
                'title' => $this->_stripTags($_brand->getTitle(), null, true),
                'url' => $_brand->getUrl(),
                'image' => $_image,
            );
        }
        
        return $_result;
    }
}
