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
    protected $_isSearch = false;

    protected function _getCategoryId() {
        if ($this->getRequest()->getParam('category_id') != 0) {
            $this->_categoryId = $this->getRequest()->getParam('category_id');
        }
        else {
            $this->_categoryId = $this->_getRootCategoryId();
            $this->_isSearch = true;
            $this->_showSubcategory = false;
        }
        
        return $this->_categoryId;
    }
    
    protected function _getRootCategoryId() {
        return Mage::getModel('core/store')->load(self::DEFAULT_STORE_ID)->getRootCategoryId();
    }

    protected function _getParams() {
        $this->_params = $this->getRequest()->getParams();
        
        return $this->_params;
    }
    
    protected function _setRegistry() {
        Mage::register(self::ACCESS_FROM, 'api');
        //Mage::register(self::CURRENT_CATEGORY, $this->_categoryId);
    }
    
    protected function _unsetRegistry() {
        Mage::unregister(self::ACCESS_FROM);
        //Mage::unregister(self::CURRENT_CATEGORY);
    }

    protected function _getCategory($_categoryId) {
        $_category = Mage::getModel('catalog/category')->load($_categoryId);
        Mage::register(self::CURRENT_CATEGORY, $_category);
        
        return $_category;
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
                    );
                }
            }

            return $_result;
        }
    }

    protected function _getAttribute($_category) {
        $_layer = $this->_isSearch ? Mage::getModel('catalogsearch/layer') : Mage::getModel('catalog/layer');
        $_layer->setCurrentCategory($_category);
        $_attributes = $_layer->getFilterableAttributes();
        $_result = array ();
        
        foreach ($_attributes as $_attribute) {
            $_attributeCode = $_attribute->getAttributeCode();
            
            if (isset ($this->_params[$_attributeCode])) {
                $this->_showSubcategory = false;
            }
                
            if ($_attributeCode == 'price') {
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
                if ($_attributeCode == 'price') {
                    $_attributeData[] = $this->_getAttributePrice($_attribute, $_option);
                }
                else {
                    $_attributeData[] = array (
                        'label' => Mage::helper('core')->escapeHtml($_option->getLabel()),
                        'value' => $_option->getValue(),
                        'count' => $_option->getCount(),
                        'checked' => $this->_getAttributeDataChecked($_attributeCode, $_option->getValue()),
                    );
                }
            }
            
            if (count($_attributeData) > 0) {
                $_result[] = array (
                    'code' => $_attribute->getAttributeCode(),
                    'label' => $_attribute->getFrontendLabel(),
                    'data' => $_attributeData,
                );
            }
        }
        
        return $_result;
    }
    
    protected function _getAttributeDataChecked($_attributeCode, $_attributeDataValue) {
        if (isset ($this->_params[$_attributeCode])) {
            if (empty ($_attributeDataValue)) {
                return true;
            }
            
            $_filter = explode(",", $this->_params[$_attributeCode]);
            $_filterAttribute = explode(",", $_attributeDataValue);

            if (count($_filter) > count($_filterAttribute)) {
                return true;
            }
        }
        
        return false;
    }

    protected function _getAttributePrice($_attribute, $_option) {
        $_min = $_option->getFilter()->getMinValue();
        $_max = $_option->getFilter()->getMaxValue();
        
        if (!$_attribute->getSliderDecimal()) {
            $_max = ceil($_max);
            $_min = floor($_min);
        }
        
        if ($_attribute->getSliderDecimal() > 0){
            $_max = number_format($_max, $_option->getSliderDecimal());
            $_min = number_format($_min, $_option->getSliderDecimal());
        }
        
        $_fromValue = $_min;
        $_toValue = $_max;
        
        //-
        $_z = floor($_min / 50000);
        
        if ($_z <= 0) {
            $_min = 0;
        }
        else {
            $_min = $_z * 50000;
        }

        //-
        $_a = round($_max / 50000, 0, PHP_ROUND_HALF_EVEN);
        $_max = $_a * 50000;
        
        //-
        $_fv = floor($_fromValue / 50000);
        
        if ($_fv <= 0) {
            $_fromValue = 0;
        }
        else {
            $_fromValue = $_fv * 50000;
        }
        
        //-
        $_tv = round($_toValue / 50000, 0, PHP_ROUND_HALF_EVEN);
        $_toValue = $_tv * 50000;

        $_from = min($_fromValue, $_min);
        $_to = max($_toValue, $_max);
        
        $_result = array (
            'min' => $_from,
            'max' => $_to,
        );
        
        return $_result;
    }
}
