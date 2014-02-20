<?php
class Phpro_Stockmonitor_Model_Resource_Eav_Mysql4_Product_Collection extends Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection {

    public $calculateSizeWithoutGroupClause = false;

    public function getSelectCountSql()
    {
        if (!$this->calculateSizeWithoutGroupClause) {
            return parent::getSelectCountSql();
        }
        $this->_renderFilters();
        $countSelect = clone $this->getSelect();
        $countSelect->reset(Zend_Db_Select::ORDER);
        $countSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $countSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
        $countSelect->reset(Zend_Db_Select::COLUMNS);
        $countSelect->reset(Zend_Db_Select::GROUP);
        $countSelect->from('', 'COUNT(DISTINCT `e`.`entity_id`)');
        return $countSelect;
    }
}
?>