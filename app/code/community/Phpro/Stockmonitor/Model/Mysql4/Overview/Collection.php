<?php
/**
 * Description of Bilna_Anzcc_Model_Resource_Grid_Collection
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Phpro_Stockmonitor_Model_Mysql4_Overview_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {
    public function _construct() {
        parent::_construct();
        $this->_init('stockmonitor/overview');
    }
    
    public function getSelectCountSql() {
        $this->_renderFilters();
        $countSelect = clone $this->getSelect();
        $countSelect->reset(Zend_Db_Select::ORDER);
        $countSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $countSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
        $countSelect->reset(Zend_Db_Select::COLUMNS);
        
        if (count($this->getSelect()->getPart(Zend_Db_Select::GROUP)) > 0) {
            $countSelect->reset(Zend_Db_Select::GROUP);
            $countSelect->distinct(true);
            $group = $this->getSelect()->getPart(Zend_Db_Select::GROUP);
            $countSelect->columns("COUNT(DISTINCT " . implode(", ", $group) . ")");
        }
        else {
            $countSelect->columns('COUNT(*)');
        }
        
        return $countSelect;
    }	
    
//    public function getSelectCountSql() {
//        $this->_renderFilters();
//
//        $countSelect = clone $this->getSelect();
//        $countSelect->reset(Zend_Db_Select::ORDER);
//        $countSelect->reset(Zend_Db_Select::LIMIT_COUNT);
//        $countSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
//        $countSelect->reset(Zend_Db_Select::COLUMNS);
//
//        if (Mage::getSingleton('adminhtml/url')->getRequest()->getModuleName() == 'Stockmonitor') {
//            $countSelect->reset(Zend_Db_Select::GROUP);
//        }
//
//        $countSelect->from('', 'COUNT(DISTINCT main_table.product_id)');
//        $countSelect->resetJoinLeft();
//
//        return $countSelect;
//    }
}
