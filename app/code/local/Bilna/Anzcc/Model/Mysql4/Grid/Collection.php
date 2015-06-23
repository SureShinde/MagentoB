<?php
/**
 * Description of Bilna_Anzcc_Model_Resource_Grid_Collection
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Anzcc_Model_Mysql4_Grid_Collection extends Mage_Sales_Model_Resource_Order_Grid_Collection {
    public function __construct() {
        parent::__construct();
    }
    
    public function getSelectCountSql() {
        $this->_renderFilters();

        $countSelect = clone $this->getSelect();
        $countSelect->reset(Zend_Db_Select::ORDER);
        $countSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $countSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
        $countSelect->reset(Zend_Db_Select::COLUMNS);

        if (Mage::getSingleton('adminhtml/url')->getRequest()->getModuleName() == 'anzcc') {
            $countSelect->reset(Zend_Db_Select::GROUP);
        }

        $countSelect->from('', 'COUNT(DISTINCT main_table.entity_id)');
        $countSelect->resetJoinLeft();

        return $countSelect;
    }
}
