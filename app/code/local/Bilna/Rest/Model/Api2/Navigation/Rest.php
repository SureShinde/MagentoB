<?php
/**
 * Description of Bilna_Rest_Model_Api2_Navigation_Rest
 *
 * @author Bilna Development Team <development@bilna.com>
 */

abstract class Bilna_Rest_Model_Api2_Navigation_Rest extends Bilna_Rest_Model_Api2_Navigation {
    const ACCESS_FROM = 'access_from';
    
    protected $_categoryId = null;
    
    protected function _setParams() {
        Mage::register(self::ACCESS_FROM, 'api');
    }

    protected function _getParams() {
        $_params = array ();
        $_params['category_id'] = $this->getRequest()->getParam('category_id');
        
        return $_params;
    }
    
    protected function _unsetParams() {
        Mage::unregister(self::ACCESS_FROM);
    }

    protected function _getCategory($_categoryId) {
        return Mage::getModel('catalog/category')->load($_categoryId);
    }

    protected function _getSubcategory($_category) {
        $_categories = $_category->getChildrenCategories();
        $_result = array ();
        
        foreach ($_categories as $_category) {
            if ($_category->getIsActive() && $_category->getProductCount()) {
                $_result[] = array (
                    'label' => Mage::helper('core')->escapeHtml($_category->getName()),
                    'value' => $_category->getId(),
                    'count' => $_category->getProductCount(),
                );
            }
        }
        
        return $_result;
    }

    protected function _getLayer($_category) {
        $_layer = Mage::getModel('catalog/layer');
        $_layer->setCurrentCategory($_category);
        $_attributes = $_layer->getFilterableAttributes();
        $_result = array ();
        
        foreach ($_attributes as $_attribute) {
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
            
            foreach ($_options->getItems() as $_option) {
                $_result[$_attribute->getAttributeCode()][] = array (
                    'label' => Mage::helper('core')->escapeHtml($_option->getLabel()),
                    'value' => $_option->getValue(),
                    'count' => $_option->getCount(),
                );
            }
        }
        
        return $_result;
    }
}
