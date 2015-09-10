<?php
/**
 * Description of Bilna_Routes_Model_Api2_Brands_Rest_Admin_V1
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Routes_Model_Api2_Brands_Rest_Admin_V1 extends Bilna_Routes_Model_Api2_Brands_Rest {
    /**
     * @api Brands Product List
     * @url /routes/brands/:path 
     * @return array
     */
    protected function _retrieve() {
        $_path = $this->parserPath($this->getRequest()->getParam('path'));
        $_brandsId = $this->getBrandsId($_path);
        $_result = array ();
        
        if (!$_brandsId) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        
        $_brands = Mage::getModel('brands/brands')->load($_brandsId);
        
        if (!$_brands->getSize() == 0) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        
        $_result = $_brands->getData();
        $_result['image'] = (string) Mage::helper('brands/image')->init($_brands)->resize($this->_imageSize);
        $_result['products'] = $this->_getProductCollection($_brandsId);
        
        return $_result;
    }
    
    /**
     * @api Brands List
     * @url /routes/brands
     * @return array
     */
    protected function _retrieveCollection() {
        $_brands = $this->_getBrandsCollection();
        
        if (!$_brands->count()) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        
        $_result = array ();
        $_result[0] = array ('total_record' => $_brands->count());
        
        foreach ($_brands as $_brand) {
            $_image = (string) Mage::helper('brands/image')->init($_brand)->resize($this->_imageSize);
            $_result[$_brand->getId()] = array (
                'title' => $this->_stripTags($_brand->getTitle(), null, true),
                'url' => $_brand->getUrl(),
                'image' => $_image,
            );
        }
        
        return $_result;
    }
}
