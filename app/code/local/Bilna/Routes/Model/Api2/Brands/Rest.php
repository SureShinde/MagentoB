<?php
/**
 * Description of Bilna_Routes_Model_Api2_Brands_Rest
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Routes_Model_Api2_Brands_Rest extends Bilna_Routes_Model_Api2_Brands {
    protected function _retrieve() {
        $path = $this->parserPath($this->getRequest()->getParam('path'));
        $brandId = $this->getBrandId($path);
        
        if (!$brandId) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        
        $brand = Mage::getModel('brands/brands')->load($brandId);
        
        if (!$brand->getSize() == 0) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        
        return $brand->getData();
    }
    
    protected function parserPath($path) {
        return str_replace('.html', '', $path);
    }

    protected function getBrandId($path) {
        return Mage::getModel('brands/brands')->checkIdentifier($path, 1);
    }
}