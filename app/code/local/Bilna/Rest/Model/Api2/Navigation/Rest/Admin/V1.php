<?php
/**
 * Description of Bilna_Rest_Model_Api2_Navigation_Rest_Admin_V1
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Rest_Model_Api2_Navigation_Rest_Admin_V1 extends Bilna_Rest_Model_Api2_Navigation_Rest {
    protected function _retrieveCollection() {
        echo "masup sini dulu";exit;
    }
    
    protected function _canShowBlock() {
        if ($this->_canShowOptions()) {
            return true;
        }
        
        $cnt = 0;
        $pos = Mage::getStoreConfig('amshopby/block/state_pos'); 
        
        if (!$this->_notInBlock($pos)) {
            $cnt = count($this->getLayer()->getState()->getFilters());
        }
        
        return $cnt;
    }
    
    /**
     * Check availability display layer options
     *
     * @return bool
     */
    protected function _canShowOptions() {
        foreach ($this->getFilters() as $filter) {
            if ($filter->getItemsCount()) {
                return true;
            }
        }

        return false;
    }
    
    /**
     * Get all layer filters
     *
     * @return array
     */
    public function getFilters() {
        $filters = array ();
        
        if ($categoryFilter = $this->_getCategoryFilter()) {
            $filters[] = $categoryFilter;
        }

        $filterableAttributes = $this->_getFilterableAttributes();
        foreach ($filterableAttributes as $attribute) {
            $filters[] = $this->getChild($attribute->getAttributeCode() . '_filter');
        }

        return $filters;
    }
}
