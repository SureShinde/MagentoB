<?php
/**
 * Description of Bilna_Rest_Model_Api2_Navigation_Rest
 *
 * @author Bilna Development Team <development@bilna.com>
 */

abstract class Bilna_Rest_Model_Api2_Navigation_Rest extends Bilna_Rest_Model_Api2_Navigation {
    const ACCESS_FROM = 'access_from';
    const CURRENT_CATEGORY = 'current_category';
    
    protected $_params = array ();
    protected $_categoryId = null;
    protected $_showSubcategory = true;

    protected function _getParams() {
        $this->_params = $this->getRequest()->getParams();
        
        return $this->_params;
    }
    
    protected function _setRegistry() {
        Mage::register(self::ACCESS_FROM, 'api');
        Mage::register(self::CURRENT_CATEGORY, $this->_categoryId);
    }
    
    protected function _unsetRegistry() {
        Mage::unregister(self::ACCESS_FROM);
        Mage::unregister(self::CURRENT_CATEGORY);
    }

    protected function _getCategory($_categoryId) {
        return Mage::getModel('catalog/category')->load($_categoryId);
    }

    protected function _getSubcategory($_category) {
        $_result = array ();
        
        if ($this->_showSubcategory) {
            $_categories = $_category->getChildrenCategories();

            foreach ($_categories as $_category) {
                if ($_category->getIsActive() && $_category->getProductCount()) {
                    $_result[] = array (
                        'label' => Mage::helper('core')->escapeHtml($_category->getName()),
                        'url' => Mage::helper('core')->escapeUrl($_category->getRequestPath()),
                        //'value' => $_category->getId(),
                        //'count' => $_category->getProductCount(),
                    );
                }
            }

            return $_result;
        }
    }

    protected function _getAttribute($_category) {
        $_layer = Mage::getModel('catalog/layer');
        $_layer->setCurrentCategory($_category);
        $_attributes = $_layer->getFilterableAttributes();
        $_result = array ();
        
        foreach ($_attributes as $_attribute) {
            if (isset ($this->_params[$_attribute->getAttributeCode()])) {
                $this->_showSubcategory = false;
            }
                
            if ($_attribute->getAttributeCode() == 'price') {
                $_filterBlockName = 'catalog/layer_filter_price';
            }
            elseif ($_attribute->getBackendType() == 'decimal') {
                $_filterBlockName = 'catalog/layer_filter_decimal';
            }
            else {
                $_filterBlockName = 'catalog/layer_filter_attribute';
            }
            
            $_options = Mage::app()->getLayout()->createBlock($_filterBlockName)->setLayer($_layer)->setAttributeModel($_attribute)->init();
            $_attributeData = array ();
            
            foreach ($_options->getItems() as $_option) {
                $_attributeData[] = array (
                //$_result[$_attribute->getAttributeCode()][] = array (
                    'label' => Mage::helper('core')->escapeHtml($_option->getLabel()),
                    'value' => $_option->getValue(),
                    'count' => $_option->getCount(),
                );
            }
            
            $_result[] = array (
                'code' => $_attribute->getAttributeCode(),
                'label' => $_attribute->getFrontendLabel(),
                'data' => $_attributeData,
            );
        }
        
        return $_result;
    }
}
