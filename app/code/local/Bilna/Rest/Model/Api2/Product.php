<?php
/**
 * Description of Bilna_Rest_Model_Api2_Product
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Rest_Model_Api2_Product extends Bilna_Rest_Model_Api2 {
    protected $_productId = null;
    protected $_categoryId = null;
    protected $_customerGroupId = null;

    protected function _getProductId() {
        if (is_null($this->_productId)) {
            $this->_productId = $this->getRequest()->getParam('product_id');
        }
        
        return $this->_productId;
    }
    
    protected function _getCategoryId() {
        if (is_null($this->_categoryId)) {
            $this->_categoryId = $this->getRequest()->getParam('category_id');
        }
        
        return $this->_categoryId;
    }
    
    protected function _getCustomerGroupId() {
        if (is_null($this->_customerGroupId)) {
            $this->_customerGroupId = $this->getRequest()->getParam('customer_group_id');
        }
        
        return $this->_customerGroupId;
    }

    /**
     * Get available attributes of API resource
     *
     * @param string $userType
     * @param string $operation
     * @return array
     */
    public function getAvailableAttributes($userType, $operation) {
        $attributes = $this->getAvailableAttributesFromConfig();
        /** @var $entityType Mage_Eav_Model_Entity_Type */
        $entityType = Mage::getModel('eav/entity_type')->loadByCode('catalog_product');
        $entityOnlyAttrs = $this->getEntityOnlyAttributes($userType, $operation);
        
        /** @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
        foreach ($entityType->getAttributeCollection() as $attribute) {
            if ($this->_isAttributeVisible($attribute, $userType)) {
                $attributes[$attribute->getAttributeCode()] = $attribute->getFrontendLabel();
            }
        }
        
        $excludedAttrs = $this->getExcludedAttributes($userType, $operation);
        $includedAttrs = $this->getIncludedAttributes($userType, $operation);
        
        foreach ($attributes as $code => $label) {
            if (in_array($code, $excludedAttrs) || ($includedAttrs && !in_array($code, $includedAttrs))) {
                unset ($attributes[$code]);
            }
            
            if (in_array($code, $entityOnlyAttrs)) {
                $attributes[$code] .= ' *';
            }
        }

        return $attributes;
    }

    /**
     * Define if attribute should be visible for passed user type
     *
     * @param Mage_Catalog_Model_Resource_Eav_Attribute $attribute
     * @param string $userType
     * @return bool
     */
    protected function _isAttributeVisible(Mage_Catalog_Model_Resource_Eav_Attribute $attribute, $userType) {
        $isAttributeVisible = false;
        
        if ($userType == Mage_Api2_Model_Auth_User_Admin::USER_TYPE) {
            $isAttributeVisible = $attribute->getIsVisible();
        }
        else {
            $systemAttributesForNonAdmin = array (
                'sku', 'name', 'short_description', 'description', 'tier_price', 'meta_title', 'meta_description',
                'meta_keyword',
            );
            
            if ($attribute->getIsUserDefined()) {
                $isAttributeVisible = $attribute->getIsVisibleOnFront();
            }
            elseif (in_array($attribute->getAttributeCode(), $systemAttributesForNonAdmin)) {
                $isAttributeVisible = true;
            }
        }
        
        return (bool) $isAttributeVisible;
    }
}
